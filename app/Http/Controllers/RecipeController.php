<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Services\IngredientMappingService;
use App\Services\TheMealDBService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    protected TheMealDBService $mealDbService;
    protected IngredientMappingService $mappingService;

    public function __construct(
        TheMealDBService $mealDbService,
        IngredientMappingService $mappingService
    ) {
        $this->mealDbService = $mealDbService;
        $this->mappingService = $mappingService;
    }

    /**
     * Pobierz listę przepisów użytkownika
     */
    public function index(Request $request)
    {
        $user = $request->attributes->get('auth_user');
        $groupId = $request->input('group_id');

        $query = Recipe::with(['ingredients.product', 'ingredients.productCategory', 'creator']);

        if ($groupId) {
            // Jeśli wybrano grupę - pokaż przepisy tej grupy + globalne
            $query->where(function($q) use ($groupId, $user) {
                $q->where('user_group_id', $groupId)
                  ->orWhereNull('user_group_id'); // Przepisy globalne (z seedera)
            });
        } else {
            // Jeśli nie wybrano grupy - pokaż przepisy użytkownika + globalne
            $query->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereNull('user_group_id'); // Przepisy globalne (z seedera)
            });
        }

        $recipes = $query->orderBy('created_at', 'desc')->get();

        // Dodaj informacje o dostępności składników
        $recipes->each(function ($recipe) use ($groupId) {
            $recipe->availability = $recipe->checkIngredientAvailability($groupId);
        });

        return response()->json([
            'success' => true,
            'recipes' => $recipes
        ]);
    }

    /**
     * Wyszukaj przepisy w TheMealDB
     */
    public function searchMealDB(Request $request)
    {
        $query = $request->input('q');
        $category = $request->input('category');
        $area = $request->input('area');

        if ($query) {
            $meals = $this->mealDbService->searchByName($query);
        } elseif ($category) {
            $meals = $this->mealDbService->getByCategory($category);
        } elseif ($area) {
            $meals = $this->mealDbService->getByArea($area);
        } else {
            // Pobierz losowe przepisy
            $meals = [];
            for ($i = 0; $i < 10; $i++) {
                $meal = $this->mealDbService->getRandomRecipe();
                if ($meal) {
                    $meals[] = $meal;
                }
            }
        }

        return response()->json([
            'success' => true,
            'meals' => $meals
        ]);
    }

    /**
     * Pobierz kategorie z TheMealDB
     */
    public function getMealDBCategories()
    {
        $categories = $this->mealDbService->getCategories();

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    /**
     * Importuj przepis z TheMealDB
     */
    public function importFromMealDB(Request $request)
    {
        $request->validate([
            'mealdb_id' => 'required|string',
            'user_group_id' => 'nullable|exists:user_groups,id',
        ]);

        $user = $request->attributes->get('auth_user');
        $mealdbId = $request->input('mealdb_id');

        // Sprawdź czy przepis już istnieje
        $existing = Recipe::where('mealdb_id', $mealdbId)
            ->where('created_by', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Ten przepis został już dodany'
            ], 400);
        }

        // Pobierz szczegóły przepisu z API
        $meal = $this->mealDbService->getRecipeById($mealdbId);

        if (!$meal) {
            return response()->json([
                'success' => false,
                'message' => 'Nie znaleziono przepisu'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Utwórz przepis
            $recipe = Recipe::create([
                'mealdb_id' => $meal['idMeal'],
                'name' => $meal['strMeal'],
                'category' => $meal['strCategory'],
                'area' => $meal['strArea'],
                'instructions' => $meal['strInstructions'],
                'thumbnail' => $meal['strMealThumb'],
                'youtube' => $meal['strYoutube'],
                'user_group_id' => $request->input('user_group_id'),
                'created_by' => $user->id,
            ]);

            // Parsuj i mapuj składniki
            $ingredients = $this->mealDbService->parseIngredients($meal);
            $mappedIngredients = $this->mappingService->mapIngredients($ingredients);

            // Zapisz składniki
            foreach ($mappedIngredients as $ingredient) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'original_name' => $ingredient['original_name'],
                    'normalized_name' => $ingredient['normalized_name'],
                    'measure' => $ingredient['measure'],
                    'product_id' => $ingredient['product_id'],
                    'product_category_id' => $ingredient['product_category_id'],
                    'estimated_quantity' => $ingredient['estimated_quantity'],
                ]);
            }

            DB::commit();

            // Załaduj relacje
            $recipe->load(['ingredients.product', 'ingredients.productCategory']);
            
            // Sprawdź dostępność składników
            $recipe->availability = $recipe->checkIngredientAvailability($request->input('user_group_id'));

            return response()->json([
                'success' => true,
                'message' => 'Przepis został dodany',
                'recipe' => $recipe
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Błąd podczas importowania przepisu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pokaż szczegóły przepisu
     */
    public function show(Request $request, $id)
    {
        $user = $request->attributes->get('auth_user');

        $recipe = Recipe::with(['ingredients.product', 'ingredients.productCategory', 'creator'])
            ->where('id', $id)
            ->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereNull('user_group_id'); // Przepisy globalne
            })
            ->first();

        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Nie znaleziono przepisu'
            ], 404);
        }

        // Sprawdź dostępność składników
        $recipe->availability = $recipe->checkIngredientAvailability($recipe->user_group_id);

        return response()->json([
            'success' => true,
            'recipe' => $recipe
        ]);
    }

    /**
     * Usuń przepis
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->attributes->get('auth_user');

        $recipe = Recipe::where('id', $id)
            ->where('created_by', $user->id)
            ->first();

        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Nie znaleziono przepisu'
            ], 404);
        }

        $recipe->delete();

        return response()->json([
            'success' => true,
            'message' => 'Przepis został usunięty'
        ]);
    }

    /**
     * Przełącz ulubione
     */
    public function toggleFavorite(Request $request, $id)
    {
        $user = $request->attributes->get('auth_user');

        $recipe = Recipe::where('id', $id)
            ->where('created_by', $user->id)
            ->first();

        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Nie znaleziono przepisu'
            ], 404);
        }

        $recipe->is_favorite = !$recipe->is_favorite;
        $recipe->save();

        return response()->json([
            'success' => true,
            'is_favorite' => $recipe->is_favorite
        ]);
    }

    /**
     * Aktualizuj mapowanie składnika
     */
    public function updateIngredientMapping(Request $request, $recipeId, $ingredientId)
    {
        $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'product_category_id' => 'nullable|exists:product_categories,id',
        ]);

        $user = $request->attributes->get('auth_user');

        $recipe = Recipe::where('id', $recipeId)
            ->where('created_by', $user->id)
            ->first();

        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Nie znaleziono przepisu'
            ], 404);
        }

        $ingredient = RecipeIngredient::where('id', $ingredientId)
            ->where('recipe_id', $recipeId)
            ->first();

        if (!$ingredient) {
            return response()->json([
                'success' => false,
                'message' => 'Nie znaleziono składnika'
            ], 404);
        }

        $ingredient->product_id = $request->input('product_id');
        $ingredient->product_category_id = $request->input('product_category_id');
        $ingredient->save();

        return response()->json([
            'success' => true,
            'message' => 'Mapowanie zostało zaktualizowane',
            'ingredient' => $ingredient->load(['product', 'productCategory'])
        ]);
    }

    /**
     * Aktualizuj przepis
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'instructions' => 'required|string',
            'ingredients' => 'required|array',
            'ingredients.*.id' => 'required',
            'ingredients.*.original_name' => 'required|string',
            'ingredients.*.measure' => 'required|string',
        ]);

        $user = $request->attributes->get('auth_user');

        $recipe = Recipe::where('id', $id)
            ->where('created_by', $user->id)
            ->first();

        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Nie znaleziono przepisu'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Aktualizuj podstawowe dane przepisu
            $recipe->name = $request->input('name');
            $recipe->instructions = $request->input('instructions');
            $recipe->save();

            // Aktualizuj składniki
            foreach ($request->input('ingredients') as $ingredientData) {
                if (isset($ingredientData['isNew']) && $ingredientData['isNew']) {
                    // Nowy składnik
                    $mappedIngredient = $this->mappingService->autoMapIngredient($ingredientData['original_name']);
                    
                    RecipeIngredient::create([
                        'recipe_id' => $recipe->id,
                        'original_name' => $ingredientData['original_name'],
                        'normalized_name' => $this->mappingService->normalizeIngredientName($ingredientData['original_name']),
                        'measure' => $ingredientData['measure'],
                        'product_id' => $mappedIngredient['product_id'],
                        'product_category_id' => $mappedIngredient['product_category_id'],
                        'estimated_quantity' => $this->mappingService->estimateQuantity($ingredientData['measure'], $ingredientData['original_name']),
                    ]);
                } else {
                    // Aktualizuj istniejący składnik
                    $ingredient = RecipeIngredient::where('id', $ingredientData['id'])
                        ->where('recipe_id', $recipe->id)
                        ->first();
                    
                    if ($ingredient) {
                        $ingredient->original_name = $ingredientData['original_name'];
                        $ingredient->measure = $ingredientData['measure'];
                        $ingredient->normalized_name = $this->mappingService->normalizeIngredientName($ingredientData['original_name']);
                        $ingredient->save();
                    }
                }
            }

            DB::commit();

            $recipe->load(['ingredients.product', 'ingredients.productCategory']);

            return response()->json([
                'success' => true,
                'message' => 'Przepis został zaktualizowany',
                'recipe' => $recipe
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Błąd podczas aktualizacji przepisu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Usuń składnik z przepisu
     */
    public function deleteIngredient(Request $request, $recipeId, $ingredientId)
    {
        $user = $request->attributes->get('auth_user');

        $recipe = Recipe::where('id', $recipeId)
            ->where('created_by', $user->id)
            ->first();

        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Nie znaleziono przepisu'
            ], 404);
        }

        $ingredient = RecipeIngredient::where('id', $ingredientId)
            ->where('recipe_id', $recipeId)
            ->first();

        if (!$ingredient) {
            return response()->json([
                'success' => false,
                'message' => 'Nie znaleziono składnika'
            ], 404);
        }

        $ingredient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Składnik został usunięty'
        ]);
    }
}
