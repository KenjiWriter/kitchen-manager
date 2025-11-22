<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Str;

class IngredientMappingService
{
    /**
     * Słownik tłumaczeń składników z angielskiego na polski
     */
    private array $translations = [
        // Mięso i drób
        'chicken' => 'kurczak',
        'chicken breast' => 'pierś z kurczaka',
        'chicken thigh' => 'udko z kurczaka',
        'chicken stock' => 'bulion z kurczaka',
        'beef' => 'wołowina',
        'ground beef' => 'mielona wołowina',
        'beef stock' => 'bulion wołowy',
        'pork' => 'wieprzowina',
        'bacon' => 'boczek',
        'sausage' => 'kiełbasa',
        'turkey' => 'indyk',
        'lamb' => 'jagnięcina',
        
        // Ryby i owoce morza
        'fish' => 'ryba',
        'salmon' => 'łosoś',
        'tuna' => 'tuńczyk',
        'cod' => 'dorsz',
        'shrimp' => 'krewetki',
        'prawns' => 'krewetki',
        'mussels' => 'małże',
        
        // Nabiał
        'milk' => 'mleko',
        'whole milk' => 'mleko pełne',
        'butter' => 'masło',
        'cheese' => 'ser',
        'cheddar' => 'ser cheddar',
        'mozzarella' => 'ser mozzarella',
        'parmesan' => 'ser parmezan',
        'cream' => 'śmietana',
        'heavy cream' => 'śmietana kremówka',
        'sour cream' => 'śmietana kwaśna',
        'yogurt' => 'jogurt',
        'eggs' => 'jaja',
        'egg' => 'jajko',
        
        // Warzywa
        'onion' => 'cebula',
        'onions' => 'cebula',
        'red onion' => 'czerwona cebula',
        'garlic' => 'czosnek',
        'garlic clove' => 'ząbek czosnku',
        'tomato' => 'pomidor',
        'tomatoes' => 'pomidory',
        'cherry tomatoes' => 'pomidorki koktajlowe',
        'tomato puree' => 'przecier pomidorowy',
        'potato' => 'ziemniak',
        'potatoes' => 'ziemniaki',
        'sweet potato' => 'batat',
        'carrot' => 'marchew',
        'carrots' => 'marchew',
        'pepper' => 'papryka',
        'bell pepper' => 'papryka',
        'red pepper' => 'czerwona papryka',
        'green pepper' => 'zielona papryka',
        'chilli' => 'chili',
        'chili' => 'chili',
        'mushroom' => 'grzyby',
        'mushrooms' => 'grzyby',
        'spinach' => 'szpinak',
        'lettuce' => 'sałata',
        'cucumber' => 'ogórek',
        'zucchini' => 'cukinia',
        'courgette' => 'cukinia',
        'eggplant' => 'bakłażan',
        'aubergine' => 'bakłażan',
        'broccoli' => 'brokuły',
        'cauliflower' => 'kalafior',
        'celery' => 'seler',
        'leek' => 'por',
        'cabbage' => 'kapusta',
        'corn' => 'kukurydza',
        'peas' => 'groszek',
        'green beans' => 'fasolka szparagowa',
        'beans' => 'fasola',
        'kidney beans' => 'fasola czerwona',
        'black beans' => 'czarna fasola',
        'chickpeas' => 'ciecierzyca',
        'lentils' => 'soczewica',
        
        // Owoce
        'apple' => 'jabłko',
        'banana' => 'banan',
        'orange' => 'pomarańcza',
        'lemon' => 'cytryna',
        'lime' => 'limonka',
        'strawberry' => 'truskawka',
        'strawberries' => 'truskawki',
        'blueberry' => 'borówka',
        'blueberries' => 'borówki',
        'raspberry' => 'malina',
        'raspberries' => 'maliny',
        'mango' => 'mango',
        'pineapple' => 'ananas',
        'grapes' => 'winogrona',
        'watermelon' => 'arbuz',
        'melon' => 'melon',
        
        // Zboża i makarony
        'rice' => 'ryż',
        'basmati rice' => 'ryż basmati',
        'brown rice' => 'ryż brązowy',
        'pasta' => 'makaron',
        'spaghetti' => 'spaghetti',
        'penne' => 'penne',
        'fusilli' => 'fusilli',
        'lasagne sheets' => 'płaty do lasagne',
        'noodles' => 'makaron',
        'flour' => 'mąka',
        'bread' => 'chleb',
        'breadcrumbs' => 'bułka tarta',
        'tortilla' => 'tortilla',
        'oats' => 'płatki owsiane',
        
        // Przyprawy i zioła
        'salt' => 'sól',
        'pepper' => 'pieprz',
        'black pepper' => 'czarny pieprz',
        'paprika' => 'papryka',
        'cumin' => 'kminek',
        'coriander' => 'kolendra',
        'oregano' => 'oregano',
        'basil' => 'bazylia',
        'thyme' => 'tymianek',
        'rosemary' => 'rozmaryn',
        'parsley' => 'pietruszka',
        'dill' => 'koperek',
        'mint' => 'mięta',
        'cinnamon' => 'cynamon',
        'ginger' => 'imbir',
        'nutmeg' => 'gałka muszkatołowa',
        'vanilla' => 'wanilia',
        'bay leaf' => 'liść laurowy',
        'curry powder' => 'curry',
        'turmeric' => 'kurkuma',
        'chili powder' => 'chili w proszku',
        'cayenne pepper' => 'pieprz cayenne',
        
        // Oleje i tłuszcze
        'oil' => 'olej',
        'olive oil' => 'oliwa z oliwek',
        'vegetable oil' => 'olej roślinny',
        'coconut oil' => 'olej kokosowy',
        'sesame oil' => 'olej sezamowy',
        
        // Inne
        'sugar' => 'cukier',
        'brown sugar' => 'brązowy cukier',
        'honey' => 'miód',
        'maple syrup' => 'syrop klonowy',
        'vinegar' => 'ocet',
        'balsamic vinegar' => 'ocet balsamiczny',
        'wine vinegar' => 'ocet winny',
        'soy sauce' => 'sos sojowy',
        'worcestershire sauce' => 'sos worcestershire',
        'tomato ketchup' => 'ketchup',
        'mustard' => 'musztarda',
        'mayonnaise' => 'majonez',
        'stock' => 'bulion',
        'vegetable stock' => 'bulion warzywny',
        'water' => 'woda',
        'wine' => 'wino',
        'red wine' => 'czerwone wino',
        'white wine' => 'białe wino',
        'beer' => 'piwo',
        'chocolate' => 'czekolada',
        'cocoa' => 'kakao',
        'nuts' => 'orzechy',
        'almonds' => 'migdały',
        'walnuts' => 'orzechy włoskie',
        'peanuts' => 'orzeszki ziemne',
        'coconut' => 'kokos',
        'coconut milk' => 'mleko kokosowe',
    ];

    /**
     * Mapowanie kategorii składników na kategorie produktów
     */
    private array $categoryMapping = [
        'chicken' => ['Mięso', 'Drób'],
        'beef' => ['Mięso', 'Wołowina'],
        'pork' => ['Mięso', 'Wieprzowina'],
        'fish' => ['Ryby', 'Owoce morza'],
        'salmon' => ['Ryby'],
        'tuna' => ['Ryby'],
        'milk' => ['Nabiał', 'Mleko'],
        'cheese' => ['Nabiał', 'Sery'],
        'butter' => ['Nabiał', 'Masło'],
        'cream' => ['Nabiał', 'Śmietana'],
        'yogurt' => ['Nabiał', 'Jogurt'],
        'egg' => ['Jaja', 'Nabiał'],
        'rice' => ['Ryż', 'Zboża'],
        'pasta' => ['Makaron', 'Zboża'],
        'flour' => ['Mąka', 'Zboża'],
        'bread' => ['Pieczywo'],
        'potato' => ['Warzywa', 'Ziemniaki'],
        'tomato' => ['Warzywa', 'Pomidory'],
        'onion' => ['Warzywa', 'Cebula'],
        'garlic' => ['Warzywa', 'Czosnek'],
        'carrot' => ['Warzywa', 'Marchew'],
        'pepper' => ['Warzywa', 'Papryka'],
        'mushroom' => ['Warzywa', 'Grzyby'],
        'apple' => ['Owoce', 'Jabłka'],
        'banana' => ['Owoce', 'Banany'],
        'orange' => ['Owoce', 'Pomarańcze'],
        'lemon' => ['Owoce', 'Cytryny'],
        'sugar' => ['Cukier', 'Słodycze'],
        'salt' => ['Przyprawy', 'Sól'],
        'oil' => ['Oleje', 'Tłuszcze'],
        'olive oil' => ['Oleje', 'Oliwa'],
    ];

    /**
     * Słowa kluczowe do rozpoznawania rodzajów mięsa/produktów
     */
    private array $keywords = [
        'chicken breast' => ['filet z kurczaka', 'pierś z kurczaka', 'chicken', 'kurczak'],
        'chicken thigh' => ['udko z kurczaka', 'udo kurczaka', 'chicken'],
        'ground beef' => ['mięso mielone', 'wołowina mielona', 'beef'],
        'beef steak' => ['stek wołowy', 'beef', 'wołowina'],
        'pork chop' => ['kotlet schabowy', 'pork', 'wieprzowina'],
        'salmon fillet' => ['filet z łososia', 'łosoś', 'salmon'],
        'white fish' => ['biała ryba', 'fish'],
        'mozzarella' => ['ser mozzarella', 'mozzarella'],
        'cheddar' => ['ser cheddar', 'cheddar'],
        'parmesan' => ['ser parmezan', 'parmezan'],
        'whole milk' => ['mleko pełne', 'milk'],
        'heavy cream' => ['śmietana kremówka', 'cream'],
        'sour cream' => ['śmietana kwaśna', 'cream'],
    ];

    /**
     * Tłumaczy nazwę składnika z angielskiego na polski
     */
    public function translateIngredient(string $name): string
    {
        $normalized = Str::lower(trim($name));
        
        // Sprawdź czy jest dokładne tłumaczenie
        if (isset($this->translations[$normalized])) {
            return ucfirst($this->translations[$normalized]);
        }
        
        // Sprawdź częściowe dopasowania (np. "fresh basil" -> "bazylia")
        foreach ($this->translations as $eng => $pl) {
            if (Str::contains($normalized, $eng)) {
                // Zachowaj dodatkowe słowa (np. "fresh" -> "świeży")
                $translated = str_replace($eng, $pl, $normalized);
                
                // Tłumaczenie słów pomocniczych
                $translated = str_replace('fresh', 'świeży', $translated);
                $translated = str_replace('dried', 'suszony', $translated);
                $translated = str_replace('chopped', 'posiekany', $translated);
                $translated = str_replace('diced', 'pokrojony w kostkę', $translated);
                $translated = str_replace('sliced', 'pokrojony w plasterki', $translated);
                $translated = str_replace('grated', 'starty', $translated);
                $translated = str_replace('minced', 'mielony', $translated);
                $translated = str_replace('ground', 'mielony', $translated);
                $translated = str_replace('powder', 'w proszku', $translated);
                $translated = str_replace('paste', 'pasta', $translated);
                $translated = str_replace('seeds', 'nasiona', $translated);
                $translated = str_replace('leaves', 'liście', $translated);
                $translated = str_replace('whole', 'cały', $translated);
                $translated = str_replace('crushed', 'kruszone', $translated);
                $translated = str_replace('flakes', 'płatki', $translated);
                
                return ucfirst(trim($translated));
            }
        }
        
        // Jeśli nie znaleziono tłumaczenia, zwróć oryginał
        return $name;
    }

    /**
     * Normalizuje nazwę składnika
     */
    public function normalizeIngredientName(string $name): string
    {
        $normalized = Str::lower(trim($name));
        
        // Usuń dodatkowe informacje w nawiasach
        $normalized = preg_replace('/\([^)]*\)/', '', $normalized);
        
        // Usuń liczby i jednostki miar
        $normalized = preg_replace('/\d+(\.\d+)?/', '', $normalized);
        $normalized = preg_replace('/(kg|g|ml|l|cup|tbsp|tsp|oz|lb)/', '', $normalized);
        
        // Usuń zbędne spacje
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        return trim($normalized);
    }

    /**
     * Automatycznie mapuje składnik do produktu lub kategorii
     */
    public function autoMapIngredient(string $ingredientName): array
    {
        $normalized = $this->normalizeIngredientName($ingredientName);
        
        $result = [
            'product_id' => null,
            'product_category_id' => null,
            'confidence' => 0,
            'suggestions' => []
        ];

        // 1. Szukaj bezpośredniego dopasowania produktu po nazwie
        $product = Product::whereRaw('LOWER(name) LIKE ?', ["%{$normalized}%"])->first();
        
        if ($product) {
            $result['product_id'] = $product->id;
            $result['confidence'] = 90;
            return $result;
        }

        // 2. Szukaj po słowach kluczowych
        foreach ($this->keywords as $key => $variations) {
            foreach ($variations as $variation) {
                if (Str::contains($normalized, $variation)) {
                    $product = Product::whereRaw('LOWER(name) LIKE ?', ["%{$variation}%"])->first();
                    
                    if ($product) {
                        $result['product_id'] = $product->id;
                        $result['confidence'] = 80;
                        return $result;
                    }
                }
            }
        }

        // 3. Szukaj po mapowaniu kategorii
        foreach ($this->categoryMapping as $keyword => $categories) {
            if (Str::contains($normalized, $keyword)) {
                foreach ($categories as $categoryName) {
                    $category = ProductCategory::whereRaw('LOWER(name) LIKE ?', ["%{$categoryName}%"])->first();
                    
                    if ($category) {
                        $result['product_category_id'] = $category->id;
                        $result['confidence'] = 60;
                        
                        // Znajdź sugestie produktów z tej kategorii
                        $result['suggestions'] = Product::where('category_id', $category->id)
                            ->limit(5)
                            ->get(['id', 'name'])
                            ->toArray();
                        
                        return $result;
                    }
                }
            }
        }

        // 4. Szukaj podobnych produktów (fuzzy matching)
        $words = explode(' ', $normalized);
        $similarProducts = [];
        
        foreach ($words as $word) {
            if (strlen($word) > 3) {
                $products = Product::whereRaw('LOWER(name) LIKE ?', ["%{$word}%"])
                    ->limit(3)
                    ->get(['id', 'name']);
                
                foreach ($products as $product) {
                    $similarProducts[$product->id] = $product;
                }
            }
        }

        if (!empty($similarProducts)) {
            $result['suggestions'] = array_values($similarProducts);
            $result['confidence'] = 40;
        }

        return $result;
    }

    /**
     * Szacuje ilość w gramach na podstawie miary
     */
    public function estimateQuantity(string $measure, string $ingredientName): float
    {
        $measure = Str::lower($measure);
        $normalized = $this->normalizeIngredientName($ingredientName);

        // Wyciągnij liczby z miary
        preg_match('/(\d+(?:\.\d+)?)\s*(\w+)?/', $measure, $matches);
        $amount = isset($matches[1]) ? (float)$matches[1] : 1;
        $unit = isset($matches[2]) ? $matches[2] : '';

        // Konwersje jednostek na gramy (przybliżone)
        $conversions = [
            'kg' => 1000,
            'g' => 1,
            'lb' => 453.592,
            'oz' => 28.3495,
            'cup' => 240, // Zależy od składnika, średnio
            'tbsp' => 15,
            'tsp' => 5,
            'ml' => 1, // Przybliżenie dla płynów
            'l' => 1000,
        ];

        foreach ($conversions as $key => $multiplier) {
            if (Str::contains($unit, $key)) {
                return $amount * $multiplier;
            }
        }

        // Jeśli brak jednostki, szacuj na podstawie składnika
        if (Str::contains($normalized, ['chicken', 'meat', 'fish'])) {
            return $amount * 200; // Średnia porcja mięsa
        }
        
        if (Str::contains($normalized, ['onion', 'potato', 'tomato'])) {
            return $amount * 150; // Średnie warzywo
        }

        return $amount * 100; // Domyślna wartość
    }

    /**
     * Batch mapowanie składników
     */
    public function mapIngredients(array $ingredients): array
    {
        $mapped = [];

        foreach ($ingredients as $ingredient) {
            $mapping = $this->autoMapIngredient($ingredient['original_name']);
            $quantity = $this->estimateQuantity(
                $ingredient['measure'] ?? '',
                $ingredient['original_name']
            );

            $mapped[] = array_merge($ingredient, [
                'normalized_name' => $this->normalizeIngredientName($ingredient['original_name']),
                'product_id' => $mapping['product_id'],
                'product_category_id' => $mapping['product_category_id'],
                'estimated_quantity' => $quantity,
                'confidence' => $mapping['confidence'],
                'suggestions' => $mapping['suggestions'],
            ]);
        }

        return $mapped;
    }
}
