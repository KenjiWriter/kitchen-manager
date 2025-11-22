<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TheMealDBService
{
    private string $baseUrl = 'https://www.themealdb.com/api/json/v1/1';

    /**
     * Wyszukaj przepisy po nazwie
     */
    public function searchByName(string $name): array
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Wyłącz weryfikację SSL (tylko dev)
            ])->get("{$this->baseUrl}/search.php", [
                's' => $name
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['meals'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('TheMealDB search error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Pobierz losowy przepis
     */
    public function getRandomRecipe(): ?array
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Wyłącz weryfikację SSL (tylko dev)
            ])->get("{$this->baseUrl}/random.php");

            if ($response->successful()) {
                $data = $response->json();
                return $data['meals'][0] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('TheMealDB random error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Pobierz przepis po ID
     */
    public function getRecipeById(string $id): ?array
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Wyłącz weryfikację SSL (tylko dev)
            ])->get("{$this->baseUrl}/lookup.php", [
                'i' => $id
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['meals'][0] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('TheMealDB lookup error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Pobierz przepisy z kategorii
     */
    public function getByCategory(string $category): array
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Wyłącz weryfikację SSL (tylko dev)
            ])->get("{$this->baseUrl}/filter.php", [
                'c' => $category
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['meals'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('TheMealDB category error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Pobierz przepisy z kuchni (area)
     */
    public function getByArea(string $area): array
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Wyłącz weryfikację SSL (tylko dev)
            ])->get("{$this->baseUrl}/filter.php", [
                'a' => $area
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['meals'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('TheMealDB area error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Pobierz wszystkie kategorie
     */
    public function getCategories(): array
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Wyłącz weryfikację SSL (tylko dev)
            ])->get("{$this->baseUrl}/categories.php");

            if ($response->successful()) {
                $data = $response->json();
                return $data['categories'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('TheMealDB categories error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Parsuj składniki z API response
     */
    public function parseIngredients(array $meal): array
    {
        $mappingService = app(IngredientMappingService::class);
        $ingredients = [];

        for ($i = 1; $i <= 20; $i++) {
            $ingredient = $meal["strIngredient{$i}"] ?? null;
            $measure = $meal["strMeasure{$i}"] ?? null;

            if ($ingredient && trim($ingredient) !== '') {
                $translatedName = $mappingService->translateIngredient(trim($ingredient));
                
                $ingredients[] = [
                    'original_name' => $translatedName, // Używamy przetłumaczonej nazwy
                    'measure' => trim($measure ?? ''),
                ];
            }
        }

        return $ingredients;
    }
}
