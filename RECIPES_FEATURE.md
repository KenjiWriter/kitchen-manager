# Feature: Przepisy i inteligentne mapowanie składników

## Przegląd

System przepisów zintegrowany z TheMealDB API z inteligentnym mapowaniem składników do produktów w magazynie.

## Kluczowe funkcje

### 1. Integracja z TheMealDB
- Przeglądanie tysięcy przepisów z różnych kuchni świata
- Wyszukiwanie przepisów po nazwie lub kategorii
- Importowanie przepisów do swojej kolekcji

### 2. Inteligentne mapowanie składników ⭐

System automatycznie rozpoznaje i mapuje składniki z przepisów na produkty w magazynie:

#### Poziomy mapowania:
1. **Bezpośrednie dopasowanie** (90% pewności)
   - Dokładna nazwa produktu: "chicken breast" → "Filet z kurczaka"

2. **Mapowanie po słowach kluczowych** (80% pewności)
   - Warianty nazw: "chicken", "kurczak" → produkty z kurczaka
   
3. **Mapowanie po kategorii** (60% pewności)
   - "salmon" → kategoria "Ryby"
   - "cheese" → kategoria "Nabiał"

4. **Fuzzy matching** (40% pewności)
   - Podobieństwo słów → sugestie produktów

### 3. Sprawdzanie dostępności składników

Dla każdego przepisu system pokazuje:
- ✅ **Dostępne składniki** - masz w wystarczającej ilości
- ⚠️ **Częściowo dostępne** - masz za mało
- ❌ **Brakujące składniki** - brak w magazynie

### 4. Automatyczne listy zakupów

Jeden klik tworzy listę zakupów z:
- Brakującymi składnikami
- Oszacowaną ilością do kupienia
- Powiązaniem z przepisem źródłowym

## Jak to działa?

### Normalizacja składników

```php
// Przykład: "200g Chicken Breast (boneless)"
// ↓ normalizacja
// "chicken breast"
// ↓ mapowanie
// Product: "Filet z kurczaka" (ID: 15)
```

### Szacowanie ilości

System konwertuje różne jednostki miar:
- `200g` → 200 gramów
- `1 cup` → ~240g
- `2 chicken breasts` → ~400g (szacunek)

### Przykładowy flow:

1. Użytkownik importuje przepis "Chicken Tikka Masala" z TheMealDB
2. System parsuje 12 składników
3. Automatyczne mapowanie:
   - "Chicken Breast" → Produkt "Filet z kurczaka" ✅
   - "Yogurt" → Kategoria "Nabiał" ⚠️
   - "Garam Masala" → Brak mapowania ❌
4. Sprawdzenie magazynu:
   - Filet: Mamy 500g, potrzeba 400g ✅
   - Jogurt: Mamy 200g, potrzeba 250g ⚠️
   - Garam Masala: Brak ❌
5. Użytkownik klika "Utwórz listę zakupów"
6. System tworzy listę z 2 pozycjami (jogurt + garam masala)

## API Endpoints

### Recipes
- `GET /api/recipes` - Lista przepisów użytkownika
- `GET /api/recipes/search-mealdb?q={query}` - Szukaj w TheMealDB
- `POST /api/recipes/import-mealdb` - Importuj przepis
- `GET /api/recipes/{id}` - Szczegóły przepisu
- `DELETE /api/recipes/{id}` - Usuń przepis
- `PUT /api/recipes/{recipeId}/ingredients/{ingredientId}` - Zaktualizuj mapowanie składnika

### Shopping Lists
- `GET /api/shopping-lists` - Lista zakupów
- `POST /api/shopping-lists/from-recipe` - Utwórz z przepisu
- `POST /api/shopping-lists/{listId}/items/{itemId}/toggle` - Zaznacz jako kupione
- `DELETE /api/shopping-lists/{id}` - Usuń listę

## Rozszerzanie systemu mapowania

### Dodawanie nowych mapowań kategorii

Edytuj `app/Services/IngredientMappingService.php`:

```php
private array $categoryMapping = [
    // Dodaj nowe mapowanie
    'mozzarella' => ['Nabiał', 'Sery'],
    'olive oil' => ['Oleje', 'Oliwa'],
];
```

### Dodawanie słów kluczowych

```php
private array $keywords = [
    'chicken breast' => ['filet z kurczaka', 'pierś z kurczaka'],
    // Dodaj warianty
];
```

## Baza danych

### Tabele
- `recipes` - Przepisy użytkownika
- `recipe_ingredients` - Składniki z mapowaniem
- `shopping_lists` - Listy zakupów
- `shopping_list_items` - Pozycje list

### Kluczowe pola w `recipe_ingredients`:
- `original_name` - Oryginalna nazwa z API
- `normalized_name` - Znormalizowana do mapowania
- `product_id` - Zmapowany produkt (może być NULL)
- `product_category_id` - Zmapowana kategoria (może być NULL)
- `estimated_quantity` - Szacowana ilość w gramach

## Widoki

- `/recipes` - Przeglądaj i zarządzaj przepisami
- `/shopping-lists` - Listy zakupów

## Możliwe ulepszenia

1. **Uczenie maszynowe** - System uczący się z manualnych poprawek użytkownika
2. **Historia zakupów** - Analiza najczęściej kupowanych składników
3. **Sugestie substytutów** - "Nie masz jogurtu? Użyj kefiru"
4. **Planowanie posiłków** - Kalendarz przepisów na tydzień
5. **Integracja z API sklepów** - Bezpośrednie zamawianie składników

## Technologie

- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Vanilla JavaScript, Tailwind CSS
- **API**: TheMealDB (bezpłatne)
- **Baza danych**: MySQL/PostgreSQL

---

Utworzone: 22 listopada 2025
