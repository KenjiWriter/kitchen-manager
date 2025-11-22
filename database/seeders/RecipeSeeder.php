<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use App\Services\IngredientMappingService;
use App\Services\TheMealDBService;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mealDbService = new TheMealDBService();
        $mappingService = new IngredientMappingService();

        // Pobierz pierwszego użytkownika (lub utwórz testowego)
        $user = User::first();
        
        if (!$user) {
            echo "❌ Brak użytkowników w bazie. Najpierw utwórz użytkownika.\n";
            return;
        }

        echo "🔍 Importowanie przepisów z TheMealDB...\n\n";

        // Lista popularnych przepisów do zaimportowania
        $searchTerms = [
            'Chicken',
            'Pasta',
            'Beef',
            'Fish',
            'Vegetarian',
        ];

        $importedCount = 0;

        foreach ($searchTerms as $term) {
            echo "📖 Szukam przepisów: {$term}...\n";
            
            $meals = $mealDbService->searchByName($term);
            
            if (empty($meals)) {
                echo "   ⚠️ Brak wyników dla: {$term}\n\n";
                continue;
            }

            // Importuj pierwsze 2 przepisy z każdej kategorii
            $mealsToImport = array_slice($meals, 0, 2);

            foreach ($mealsToImport as $mealSummary) {
                // Pobierz pełne szczegóły przepisu
                $meal = $mealDbService->getRecipeById($mealSummary['idMeal']);
                
                if (!$meal) {
                    continue;
                }

                // Sprawdź czy przepis już istnieje
                $existing = Recipe::where('mealdb_id', $meal['idMeal'])->first();
                if ($existing) {
                    echo "   ⏭️  Przepis już istnieje: {$meal['strMeal']}\n";
                    continue;
                }

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
                        'user_group_id' => null, // Przepisy seedowane są globalne
                        'created_by' => $user->id,
                    ]);

                    // Parsuj i mapuj składniki
                    $ingredients = $mealDbService->parseIngredients($meal);
                    $mappedIngredients = $mappingService->mapIngredients($ingredients);

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

                    echo "   ✅ Zaimportowano: {$meal['strMeal']} ({$meal['strCategory']})\n";
                    echo "      Składniki: " . count($ingredients) . " | Zmapowane: " . count(array_filter($mappedIngredients, fn($i) => $i['product_id'] || $i['product_category_id'])) . "\n";
                    
                    $importedCount++;

                } catch (\Exception $e) {
                    echo "   ❌ Błąd: {$meal['strMeal']} - " . $e->getMessage() . "\n";
                }
            }

            echo "\n";
            
            // Krótka przerwa między requestami do API
            usleep(500000); // 0.5 sekundy
        }

        echo "\n✨ Zaimportowano {$importedCount} przepisów!\n";
        
        if ($importedCount > 0) {
            echo "\n📊 Statystyki:\n";
            echo "   Łącznie przepisów: " . Recipe::count() . "\n";
            echo "   Łącznie składników: " . RecipeIngredient::count() . "\n";
            
            $mappedCount = RecipeIngredient::whereNotNull('product_id')
                ->orWhereNotNull('product_category_id')
                ->count();
            $totalIngredients = RecipeIngredient::count();
            $percentage = $totalIngredients > 0 ? round(($mappedCount / $totalIngredients) * 100) : 0;
            
            echo "   Zmapowane składniki: {$mappedCount} / {$totalIngredients} ({$percentage}%)\n";
        }
    }
}
