<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Przepisy - Inwentarz Kuchenny</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen pb-20">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Przepisy</h1>
                        <p class="text-sm text-gray-500" id="recipeCount">Ładowanie...</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('shopping-lists') }}"
                        class="bg-orange-100 text-orange-700 p-2 rounded-lg hover:bg-orange-200 transition active:scale-95"
                        title="Listy zakupów">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </a>
                    <button onclick="switchTab('discover')"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition active:scale-95">
                        + Dodaj przepis
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Tabs -->
    <div class="bg-white border-b border-gray-200 sticky top-[73px] z-40">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex space-x-4">
                <button onclick="switchTab('my-recipes')"
                    class="tab-button active px-4 py-3 text-sm font-medium border-b-2 transition" data-tab="my-recipes">
                    Moje przepisy
                </button>
                <button onclick="switchTab('discover')"
                    class="tab-button px-4 py-3 text-sm font-medium border-b-2 transition" data-tab="discover">
                    Odkrywaj
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Content: My Recipes -->
    <div id="my-recipes-tab" class="tab-content">
        <!-- Search -->
        <div class="max-w-7xl mx-auto px-4 py-4">
            <input type="text" id="myRecipesSearch" placeholder="Szukaj w moich przepisach..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <!-- Loading State -->
        <div id="myRecipesLoading" class="max-w-7xl mx-auto px-4 py-8">
            <div class="text-center text-gray-500">
                <svg class="animate-spin h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p>Ładowanie przepisów...</p>
            </div>
        </div>

        <!-- Empty State -->
        <div id="myRecipesEmpty" class="hidden max-w-7xl mx-auto px-4 py-16 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                </path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Brak przepisów</h3>
            <p class="text-gray-500 mb-4">Dodaj swój pierwszy przepis z bazy TheMealDB</p>
            <button onclick="switchTab('discover')"
                class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700">
                Przeglądaj przepisy
            </button>
        </div>

        <!-- Recipes List -->
        <div id="myRecipesList"
            class="hidden max-w-7xl mx-auto px-4 py-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        </div>
    </div>

    <!-- Tab Content: Discover -->
    <div id="discover-tab" class="tab-content hidden">
        <!-- Search TheMealDB -->
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex space-x-2">
                <input type="text" id="mealdbSearch" placeholder="Szukaj przepisów..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
                <button onclick="searchMealDB()"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700">
                    Szukaj
                </button>
            </div>
        </div>

        <!-- Categories Filter -->
        <div class="max-w-7xl mx-auto px-4 pb-4">
            <div class="flex overflow-x-auto space-x-2 pb-2 scrollbar-hide">
                <button onclick="filterMealDBByCategory(null)"
                    class="category-filter active flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition"
                    data-category="all">
                    Wszystkie
                </button>
                <div id="mealdbCategories"></div>
            </div>
        </div>

        <!-- Discover Results -->
        <div id="discoverResults" class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        </div>
    </div>

    <!-- Recipe Detail Modal -->
    <div id="recipeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto">
        <div class="min-h-screen px-4 flex items-center justify-center">
            <div class="bg-white w-full max-w-4xl rounded-lg shadow-xl my-8">
                <div
                    class="sticky top-0 bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between rounded-t-lg">
                    <h2 class="text-lg font-bold" id="recipeModalTitle">Szczegóły przepisu</h2>
                    <button onclick="closeRecipeModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <div id="recipeModalContent" class="p-4"></div>
            </div>
        </div>
    </div>

    <style>
        .tab-button {
            color: #6B7280;
            border-color: transparent;
        }

        .tab-button.active {
            color: #3B82F6;
            border-color: #3B82F6;
        }

        .tab-content {
            display: block;
        }

        .tab-content.hidden {
            display: none;
        }

        .category-filter {
            background-color: #F3F4F6;
            color: #6B7280;
        }

        .category-filter.active {
            background-color: #3B82F6;
            color: white;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .ingredient-available {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .ingredient-partial {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .ingredient-missing {
            background-color: #FEE2E2;
            color: #991B1B;
        }
    </style>

    <script>
        const token = localStorage.getItem('auth_token');
        let myRecipes = [];
        let mealdbResults = [];
        let mealdbCategories = [];
        let currentRecipe = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadMyRecipes();
            loadMealDBCategories();
            loadRandomMeals();

            // Search handlers
            document.getElementById('myRecipesSearch').addEventListener('input', filterMyRecipes);
            document.getElementById('mealdbSearch').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') searchMealDB();
            });
        });

        // Tab switching
        function switchTab(tab) {
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-tab="${tab}"]`).classList.add('active');

            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById(`${tab}-tab`).classList.remove('hidden');

            if (tab === 'discover' && mealdbResults.length === 0) {
                loadRandomMeals();
            }
        }

        // Load my recipes
        async function loadMyRecipes() {
            try {
                const response = await fetch('/api/recipes', {
                    headers: {
                        'X-Auth-Token': token,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                myRecipes = data.recipes || [];

                document.getElementById('myRecipesLoading').classList.add('hidden');

                if (myRecipes.length === 0) {
                    document.getElementById('myRecipesEmpty').classList.remove('hidden');
                    document.getElementById('recipeCount').textContent = 'Brak przepisów';
                } else {
                    document.getElementById('myRecipesList').classList.remove('hidden');
                    document.getElementById('recipeCount').textContent = `${myRecipes.length} przepisów`;
                    renderMyRecipes();
                }
            } catch (error) {
                console.error('Load recipes error:', error);
            }
        }

        // Render my recipes
        function renderMyRecipes() {
            const container = document.getElementById('myRecipesList');
            const searchTerm = document.getElementById('myRecipesSearch').value.toLowerCase();

            let filtered = myRecipes;
            if (searchTerm) {
                filtered = myRecipes.filter(recipe =>
                    recipe.name.toLowerCase().includes(searchTerm) ||
                    recipe.category?.toLowerCase().includes(searchTerm)
                );
            }

            container.innerHTML = filtered.map(recipe => {
                const available = recipe.availability?.available?.length || 0;
                const missing = recipe.availability?.missing?.length || 0;
                const total = available + missing + (recipe.availability?.partial?.length || 0);
                const percentage = total > 0 ? Math.round((available / total) * 100) : 0;

                return `
                    <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer hover:shadow-lg transition"
                        onclick="showRecipeDetail(${recipe.id})">
                        <img src="${recipe.thumbnail || '/images/placeholder.jpg'}" alt="${recipe.name}"
                            class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2">${recipe.name}</h3>
                            <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                                <span>${recipe.category || 'Inne'}</span>
                                <span>${recipe.area || ''}</span>
                            </div>
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span>Dostępność składników</span>
                                    <span class="font-medium">${percentage}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full transition-all" 
                                        style="width: ${percentage}%"></div>
                                </div>
                                ${missing > 0 ? `
                                        <p class="text-xs text-red-600 mt-2">Brakuje: ${missing} składników</p>
                                    ` : `
                                        <p class="text-xs text-green-600 mt-2">✓ Wszystkie składniki dostępne</p>
                                    `}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function filterMyRecipes() {
            renderMyRecipes();
        }

        // Load MealDB categories
        async function loadMealDBCategories() {
            try {
                const response = await fetch('/api/recipes/mealdb-categories', {
                    headers: {
                        'X-Auth-Token': token,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                mealdbCategories = data.categories || [];
                renderMealDBCategories();
            } catch (error) {
                console.error('Load categories error:', error);
            }
        }

        function renderMealDBCategories() {
            const container = document.getElementById('mealdbCategories');
            container.innerHTML = mealdbCategories.map(cat => `
                <button onclick="filterMealDBByCategory('${cat.strCategory}')"
                    class="category-filter flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition"
                    data-category="${cat.strCategory}">
                    ${cat.strCategory}
                </button>
            `).join('');
        }

        async function filterMealDBByCategory(category) {
            document.querySelectorAll('.category-filter').forEach(btn => {
                btn.classList.remove('active');
            });

            if (category) {
                document.querySelector(`[data-category="${category}"]`).classList.add('active');

                const response = await fetch(`/api/recipes/search-mealdb?category=${category}`, {
                    headers: {
                        'X-Auth-Token': token,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                mealdbResults = data.meals || [];
            } else {
                document.querySelector('[data-category="all"]').classList.add('active');
                await loadRandomMeals();
            }

            renderMealDBResults();
        }

        // Load random meals
        async function loadRandomMeals() {
            try {
                const response = await fetch('/api/recipes/search-mealdb', {
                    headers: {
                        'X-Auth-Token': token,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                mealdbResults = data.meals || [];
                renderMealDBResults();
            } catch (error) {
                console.error('Load random meals error:', error);
            }
        }

        // Search MealDB
        async function searchMealDB() {
            const query = document.getElementById('mealdbSearch').value;

            try {
                const response = await fetch(`/api/recipes/search-mealdb?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Auth-Token': token,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                mealdbResults = data.meals || [];
                renderMealDBResults();
            } catch (error) {
                console.error('Search error:', error);
            }
        }

        // Render MealDB results
        function renderMealDBResults() {
            const container = document.getElementById('discoverResults');

            if (mealdbResults.length === 0) {
                container.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500">Brak wyników</div>';
                return;
            }

            container.innerHTML = mealdbResults.map(meal => `
                <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer hover:shadow-lg transition"
                    onclick="importMeal('${meal.idMeal}')">
                    <img src="${meal.strMealThumb || '/images/placeholder.jpg'}" alt="${meal.strMeal}"
                        class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-2">${meal.strMeal}</h3>
                        <div class="flex items-center justify-between text-sm text-gray-600">
                            <span>${meal.strCategory || 'Inne'}</span>
                            <span>${meal.strArea || ''}</span>
                        </div>
                        <button class="mt-3 w-full bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition"
                            onclick="event.stopPropagation(); importMeal('${meal.idMeal}')">
                            Dodaj do moich przepisów
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Import meal from MealDB
        async function importMeal(mealdbId) {
            if (!confirm('Czy chcesz dodać ten przepis do swojej kolekcji?')) return;

            try {
                const response = await fetch('/api/recipes/import-mealdb', {
                    method: 'POST',
                    headers: {
                        'X-Auth-Token': token,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        mealdb_id: mealdbId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Przepis został dodany!');
                    loadMyRecipes();
                    switchTab('my-recipes');
                } else {
                    alert(data.message || 'Błąd podczas dodawania przepisu');
                }
            } catch (error) {
                console.error('Import error:', error);
                alert('Wystąpił błąd podczas importowania przepisu');
            }
        }

        // Show recipe detail
        async function showRecipeDetail(recipeId) {
            try {
                const response = await fetch(`/api/recipes/${recipeId}`, {
                    headers: {
                        'X-Auth-Token': token,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                currentRecipe = data.recipe;
                renderRecipeDetail();
                document.getElementById('recipeModal').classList.remove('hidden');
            } catch (error) {
                console.error('Load recipe error:', error);
            }
        }

        function renderRecipeDetail() {
            if (!currentRecipe) return;

            const availability = currentRecipe.availability || {
                available: [],
                missing: [],
                partial: []
            };
            const hasAllIngredients = availability.missing.length === 0 && availability.partial.length === 0;

            document.getElementById('recipeModalTitle').textContent = currentRecipe.name;
            document.getElementById('recipeModalContent').innerHTML = `
                <img src="${currentRecipe.thumbnail}" alt="${currentRecipe.name}" 
                    class="w-full h-64 object-cover rounded-lg mb-4">
                
                <div class="mb-4">
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm mr-2">
                        ${currentRecipe.category}
                    </span>
                    <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                        ${currentRecipe.area}
                    </span>
                </div>

                <h3 class="font-bold text-lg mb-3">Składniki</h3>
                <div class="space-y-2 mb-6">
                    ${currentRecipe.ingredients.map(ing => {
                        let statusClass = 'ingredient-missing';
                        let statusText = '✗ Brak';
                        
                        const avail = availability.available.find(a => a.ingredient.id === ing.id);
                        const partial = availability.partial.find(a => a.ingredient.id === ing.id);
                        
                        if (avail) {
                            statusClass = 'ingredient-available';
                            statusText = '✓ Dostępne';
                        } else if (partial) {
                            statusClass = 'ingredient-partial';
                            statusText = '⚠ Częściowo';
                        }

                        const isMapped = ing.product || ing.product_category;

                        return ` <
                div class = "flex items-start justify-between p-3 rounded-lg ${statusClass}" >
                <
                div class = "flex-1" >
                <
                span class = "font-medium" > $ {
                    ing.original_name
                } < /span> <
                span class = "text-sm ml-2" > ($ {
                    ing.measure
                }) < /span>
            $ {
                ing.product ? `
                                        <div class="text-xs mt-1 text-gray-600">Zmapowano: ${ing.product.name}</div>
                                    ` : ing.product_category ? `
                                        <div class="text-xs mt-1 text-gray-600">Kategoria: ${ing.product_category.name}</div>
                                    ` : `
                                        <div class="text-xs mt-1 text-gray-500">Nie zmapowano</div>
                                    `
            } <
            /div> <
            div class = "flex items-center space-x-2" >
            <
            span class = "text-sm font-medium whitespace-nowrap" > $ {
                statusText
            } < /span>
            $ {
                !isMapped ? `
                                        <button onclick="event.stopPropagation(); showMappingModal(${ing.id}, '${ing.original_name.replace(/'/g, "\\'")}')"
                                            class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition whitespace-nowrap">
                                            Przypisz
                                        </button>
                                    ` : ''
            } <
            /div> <
            /div>
            `;
                    }).join('')}
                </div>

                ${!hasAllIngredients ? `
                        <button onclick="createShoppingList()" 
                            class="w-full mb-4 bg-orange-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-orange-700 transition">
                            🛒 Utwórz listę zakupów (${availability.missing.length + availability.partial.length} pozycji)
                        </button>
                    ` : ''}

                <button onclick="editRecipe()" 
                    class="w-full mb-4 bg-blue-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                    ✏️ Edytuj przepis
                </button>

                <h3 class="font-bold text-lg mb-3">Instrukcje</h3>
                <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap mb-6">
                    ${currentRecipe.instructions}
                </div>

                ${currentRecipe.youtube ? `
                        <a href="${currentRecipe.youtube}" target="_blank"
                            class="block w-full text-center bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition mb-4">
                            📺 Obejrzyj na YouTube
                        </a>
                    ` : ''}

                <button onclick="deleteRecipe()" 
                    class="w-full bg-red-100 text-red-600 px-4 py-2 rounded-lg font-medium hover:bg-red-200 transition">
                    Usuń przepis
                </button>
            `;
        }

        // Create shopping list
        async function createShoppingList() {
            if (!currentRecipe) return;

            try {
                const response = await fetch('/api/shopping-lists/from-recipe', {
                    method: 'POST',
                    headers: {
                        'X-Auth-Token': token,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        recipe_id: currentRecipe.id
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(`Lista zakupów została utworzona!\nDodano ${data.shopping_list.items.length} pozycji.`);
                    closeRecipeModal();
                } else {
                    alert(data.message || 'Błąd podczas tworzenia listy');
                }
            } catch (error) {
                console.error('Create shopping list error:', error);
                alert('Wystąpił błąd podczas tworzenia listy zakupów');
            }
        }

        // Delete recipe
        async function deleteRecipe() {
            if (!currentRecipe) return;
            if (!confirm('Czy na pewno chcesz usunąć ten przepis?')) return;

            try {
                const response = await fetch(`/api/recipes/${currentRecipe.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Auth-Token': token,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert('Przepis został usunięty');
                    closeRecipeModal();
                    loadMyRecipes();
                } else {
                    alert(data.message || 'Błąd podczas usuwania przepisu');
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('Wystąpił błąd podczas usuwania przepisu');
            }
        }

        function closeRecipeModal() {
            document.getElementById('recipeModal').classList.add('hidden');
            currentRecipe = null;
        }
    </script>

    <!-- Mapping Modal -->
    <div id="mappingModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h2 class="text-lg font-bold" id="mappingModalTitle">Przypisz składnik do produktu</h2>
                <button onclick="closeMappingModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-4">Składnik: <strong id="mappingIngredientName"></strong></p>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Wyszukaj produkt</label>
                    <input type="text" id="productSearch" placeholder="Wpisz nazwę produktu..."
                        onkeyup="searchProducts()" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <div id="productSearchResults" class="space-y-2 mb-4">
                    <!-- Wyniki wyszukiwania -->
                </div>

                <div class="flex space-x-2">
                    <button onclick="closeMappingModal()"
                        class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-300 transition">
                        Anuluj
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Recipe Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto">
        <div class="min-h-screen px-4 flex items-center justify-center">
            <div class="bg-white w-full max-w-4xl rounded-lg shadow-xl my-8">
                <div
                    class="sticky top-0 bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between rounded-t-lg">
                    <h2 class="text-lg font-bold">Edytuj przepis</h2>
                    <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nazwa przepisu</label>
                        <input type="text" id="editRecipeName"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Instrukcje</label>
                        <textarea id="editRecipeInstructions" rows="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-700">Składniki</label>
                            <button onclick="addIngredient()"
                                class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                + Dodaj składnik
                            </button>
                        </div>
                        <div id="editIngredientsList" class="space-y-2"></div>
                    </div>

                    <div class="flex space-x-2">
                        <button onclick="closeEditModal()"
                            class="flex-1 bg-gray-200 text-gray-700 px-4 py-3 rounded-lg font-medium hover:bg-gray-300 transition">
                            Anuluj
                        </button>
                        <button onclick="saveRecipe()"
                            class="flex-1 bg-blue-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                            Zapisz zmiany
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentIngredientId = null;
        let productSearchTimeout = null;
        let allProducts = [];
        let editMode = false;

        // Load all products
        async function loadAllProducts() {
            try {
                const response = await fetch('/api/products', {
                    headers: {
                        'X-Auth-Token': token,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                allProducts = data.products || [];
            } catch (error) {
                console.error('Load products error:', error);
            }
        }

        // Show mapping modal
        function showMappingModal(ingredientId, ingredientName) {
            currentIngredientId = ingredientId;
            document.getElementById('mappingIngredientName').textContent = ingredientName;
            document.getElementById('productSearch').value = ingredientName;
            document.getElementById('mappingModal').classList.remove('hidden');

            if (allProducts.length === 0) {
                loadAllProducts().then(() => searchProducts());
            } else {
                searchProducts();
            }
        }

        // Close mapping modal
        function closeMappingModal() {
            document.getElementById('mappingModal').classList.add('hidden');
            currentIngredientId = null;
        }

        // Search products
        function searchProducts() {
            clearTimeout(productSearchTimeout);

            productSearchTimeout = setTimeout(() => {
                const query = document.getElementById('productSearch').value.toLowerCase();
                const container = document.getElementById('productSearchResults');

                if (!query) {
                    container.innerHTML =
                        '<p class="text-sm text-gray-500 text-center py-4">Wpisz nazwę produktu</p>';
                    return;
                }

                const filtered = allProducts.filter(p =>
                    p.name.toLowerCase().includes(query) ||
                    (p.category && p.category.name.toLowerCase().includes(query))
                );

                if (filtered.length === 0) {
                    container.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">Brak wyników</p>';
                    return;
                }

                container.innerHTML = filtered.slice(0, 10).map(product => `
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                        onclick="mapIngredientToProduct(${product.id}, '${product.name.replace(/'/g, "\\'")}')">
                        <div>
                            <div class="font-medium">${product.name}</div>
                            ${product.category ? `<div class="text-xs text-gray-500">${product.category.name}</div>` : ''}
                        </div>
                        <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                            Wybierz
                        </button>
                    </div>
                `).join('');
            }, 300);
        }

        // Map ingredient to product
        async function mapIngredientToProduct(productId, productName) {
            if (!currentIngredientId || !currentRecipe) return;

            try {
                const response = await fetch(`/api/recipes/${currentRecipe.id}/ingredients/${currentIngredientId}`, {
                    method: 'PUT',
                    headers: {
                        'X-Auth-Token': token,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(`Składnik został przypisany do produktu: ${productName}`);
                    closeMappingModal();
                    // Reload recipe to refresh ingredients
                    showRecipeDetail(currentRecipe.id);
                } else {
                    alert(data.message || 'Błąd podczas przypisywania składnika');
                }
            } catch (error) {
                console.error('Map ingredient error:', error);
                alert('Wystąpił błąd podczas przypisywania składnika');
            }
        }

        // Edit recipe functions
        function editRecipe() {
            if (!currentRecipe) return;

            document.getElementById('editRecipeName').value = currentRecipe.name;
            document.getElementById('editRecipeInstructions').value = currentRecipe.instructions;

            renderEditIngredients();

            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function renderEditIngredients() {
            if (!currentRecipe) return;

            const container = document.getElementById('editIngredientsList');
            container.innerHTML = currentRecipe.ingredients.map((ing, idx) => `
                <div class="flex items-start space-x-2 p-3 bg-gray-50 rounded-lg" data-ingredient-id="${ing.id}">
                    <div class="flex-1">
                        <input type="text" value="${ing.original_name}" 
                            onchange="updateIngredientName(${ing.id}, this.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded mb-2" placeholder="Nazwa składnika">
                        <input type="text" value="${ing.measure}" 
                            onchange="updateIngredientMeasure(${ing.id}, this.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="Ilość (np. 200g)">
                    </div>
                    <button onclick="removeIngredient(${ing.id})" class="text-red-600 hover:text-red-800 p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            `).join('');
        }

        function updateIngredientName(ingredientId, newName) {
            const ingredient = currentRecipe.ingredients.find(i => i.id === ingredientId);
            if (ingredient) {
                ingredient.original_name = newName;
            }
        }

        function updateIngredientMeasure(ingredientId, newMeasure) {
            const ingredient = currentRecipe.ingredients.find(i => i.id === ingredientId);
            if (ingredient) {
                ingredient.measure = newMeasure;
            }
        }

        async function removeIngredient(ingredientId) {
            if (!confirm('Czy na pewno chcesz usunąć ten składnik?')) return;

            try {
                const response = await fetch(`/api/recipes/${currentRecipe.id}/ingredients/${ingredientId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Auth-Token': token,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    currentRecipe.ingredients = currentRecipe.ingredients.filter(i => i.id !== ingredientId);
                    renderEditIngredients();
                } else {
                    alert(data.message || 'Błąd podczas usuwania składnika');
                }
            } catch (error) {
                console.error('Remove ingredient error:', error);
                alert('Wystąpił błąd podczas usuwania składnika');
            }
        }

        function addIngredient() {
            if (!currentRecipe) return;

            const newIngredient = {
                id: 'new_' + Date.now(),
                original_name: '',
                measure: '',
                isNew: true
            };

            currentRecipe.ingredients.push(newIngredient);
            renderEditIngredients();
        }

        async function saveRecipe() {
            if (!currentRecipe) return;

            const updatedRecipe = {
                name: document.getElementById('editRecipeName').value,
                instructions: document.getElementById('editRecipeInstructions').value,
                ingredients: currentRecipe.ingredients.map(ing => ({
                    id: ing.id,
                    original_name: ing.original_name,
                    measure: ing.measure,
                    isNew: ing.isNew
                }))
            };

            try {
                const response = await fetch(`/api/recipes/${currentRecipe.id}`, {
                    method: 'PUT',
                    headers: {
                        'X-Auth-Token': token,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(updatedRecipe)
                });

                const data = await response.json();

                if (data.success) {
                    alert('Przepis został zaktualizowany!');
                    closeEditModal();
                    showRecipeDetail(currentRecipe.id);
                    loadMyRecipes();
                } else {
                    alert(data.message || 'Błąd podczas zapisywania przepisu');
                }
            } catch (error) {
                console.error('Save recipe error:', error);
                alert('Wystąpił błąd podczas zapisywania przepisu');
            }
        }
    </script>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-around">
                <a href="{{ route('dashboard') }}"
                    class="flex flex-col items-center py-3 text-gray-600 hover:text-blue-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    <span class="text-xs mt-1">Home</span>
                </a>
                <a href="{{ route('pantry') }}"
                    class="flex flex-col items-center py-3 text-gray-600 hover:text-blue-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span class="text-xs mt-1">Spiżarnia</span>
                </a>
                <a href="{{ route('recipes') }}" class="flex flex-col items-center py-3 text-blue-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                    <span class="text-xs mt-1">Przepisy</span>
                </a>
                <a href="{{ route('scanner') }}"
                    class="flex flex-col items-center py-3 text-gray-600 hover:text-blue-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                        </path>
                    </svg>
                    <span class="text-xs mt-1">Skaner</span>
                </a>
            </div>
        </div>
    </nav>
</body>

</html>
