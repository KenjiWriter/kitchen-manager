<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShoppingListController extends Controller
{
    /**
     * Pobierz listy zakupów
     */
    public function index(Request $request)
    {
        $user = $request->attributes->get('auth_user');
        $groupId = $request->input('group_id');

        $query = ShoppingList::with(['items', 'creator']);

        if ($groupId) {
            $query->where('user_group_id', $groupId);
        } else {
            $query->where('created_by', $user->id);
        }

        $lists = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'shopping_lists' => $lists
        ]);
    }

    /**
     * Utwórz listę zakupów z przepisu
     */
    public function createFromRecipe(Request $request)
    {
        $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'name' => 'nullable|string|max:255',
        ]);

        $user = $request->attributes->get('auth_user');
        $recipeId = $request->input('recipe_id');

        $recipe = Recipe::with('ingredients.product')
            ->where('id', $recipeId)
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

        DB::beginTransaction();
        try {
            // Utwórz listę zakupów
            $shoppingList = ShoppingList::create([
                'user_group_id' => $recipe->user_group_id,
                'created_by' => $user->id,
                'name' => $request->input('name') ?? "Lista do: {$recipe->name}",
                'notes' => "Składniki do przepisu: {$recipe->name}",
            ]);

            // Sprawdź dostępność składników
            $availability = $recipe->checkIngredientAvailability($recipe->user_group_id);

            // Dodaj brakujące i częściowo dostępne składniki
            $missingIngredients = array_merge(
                $availability['missing'],
                $availability['partial']
            );

            foreach ($missingIngredients as $item) {
                $ingredient = $item['ingredient'];
                $status = $item['status'];

                $quantityNeeded = $ingredient->estimated_quantity - ($status['total_quantity'] ?? 0);
                
                ShoppingListItem::create([
                    'shopping_list_id' => $shoppingList->id,
                    'recipe_ingredient_id' => $ingredient->id,
                    'name' => $ingredient->original_name,
                    'quantity' => $ingredient->measure . ($quantityNeeded > 0 ? " (brakuje ~{$quantityNeeded}g)" : ''),
                ]);
            }

            DB::commit();

            $shoppingList->load('items');

            return response()->json([
                'success' => true,
                'message' => 'Lista zakupów została utworzona',
                'shopping_list' => $shoppingList
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Błąd podczas tworzenia listy: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Utwórz pustą listę zakupów
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_group_id' => 'required|exists:user_groups,id',
            'notes' => 'nullable|string',
        ]);

        $user = $request->attributes->get('auth_user');

        $shoppingList = ShoppingList::create([
            'user_group_id' => $request->input('user_group_id'),
            'created_by' => $user->id,
            'name' => $request->input('name'),
            'notes' => $request->input('notes'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lista zakupów została utworzona',
            'shopping_list' => $shoppingList
        ]);
    }

    /**
     * Dodaj element do listy zakupów
     */
    public function addItem(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|string|max:255',
        ]);

        $shoppingList = ShoppingList::findOrFail($id);

        $item = ShoppingListItem::create([
            'shopping_list_id' => $shoppingList->id,
            'name' => $request->input('name'),
            'quantity' => $request->input('quantity'),
        ]);

        return response()->json([
            'success' => true,
            'item' => $item
        ]);
    }

    /**
     * Oznacz element jako kupiony/niekupiony
     */
    public function toggleItem(Request $request, $listId, $itemId)
    {
        $item = ShoppingListItem::where('shopping_list_id', $listId)
            ->where('id', $itemId)
            ->firstOrFail();

        $item->is_checked = !$item->is_checked;
        $item->save();

        return response()->json([
            'success' => true,
            'is_checked' => $item->is_checked
        ]);
    }

    /**
     * Usuń element z listy
     */
    public function deleteItem(Request $request, $listId, $itemId)
    {
        ShoppingListItem::where('shopping_list_id', $listId)
            ->where('id', $itemId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Element został usunięty'
        ]);
    }

    /**
     * Usuń listę zakupów
     */
    public function destroy($id)
    {
        ShoppingList::destroy($id);

        return response()->json([
            'success' => true,
            'message' => 'Lista zakupów została usunięta'
        ]);
    }
}
