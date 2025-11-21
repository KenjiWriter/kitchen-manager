<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Produkty - Inwentarz Kuchenny</title>
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
                        <h1 class="text-lg font-bold text-gray-900">Produkty</h1>
                        <p class="text-sm text-gray-500" id="productCount">Ładowanie...</p>
                    </div>
                </div>
                <a href="{{ route('products.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition active:scale-95">
                    + Dodaj
                </a>
            </div>
        </div>
    </header>

    <!-- Categories Filter -->
    <div class="bg-white border-b border-gray-200 sticky top-[73px] z-40">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex overflow-x-auto space-x-2 pb-2 -mb-2 scrollbar-hide">
                <button onclick="filterByCategory(null)"
                    class="category-filter flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition active"
                    data-category="all">
                    Wszystkie
                </button>
                <div id="categoriesFilter"></div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="relative">
            <input type="text" id="searchInput" placeholder="Szukaj po nazwie lub kodzie EAN..."
                class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 pb-6">
        <!-- Loading State -->
        <div id="loadingState" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="text-gray-600 mt-4">Ładowanie produktów...</p>
        </div>

        <!-- Products Grid -->
        <div id="productsList" class="hidden grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"></div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden bg-white rounded-lg shadow p-8 text-center">
            <span class="text-5xl mb-4 block">📦</span>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Brak produktów</h3>
            <p class="text-gray-600 mb-6">Dodaj swój pierwszy produkt do bazy</p>
            <a href="{{ route('products.create') }}"
                class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                Dodaj produkt
            </a>
        </div>

        <!-- No Results State -->
        <div id="noResultsState" class="hidden bg-white rounded-lg shadow p-8 text-center">
            <span class="text-5xl mb-4 block">🔍</span>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Brak wyników</h3>
            <p class="text-gray-600">Spróbuj zmienić kryteria wyszukiwania</p>
        </div>
    </main>

    <!-- Edit Product Modal -->
    <div id="editProductModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 overflow-y-auto">
        <div class="bg-white rounded-lg max-w-md w-full p-6 my-8">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-xl font-bold">Edytuj produkt</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="editProductForm" class="space-y-4">
                <input type="hidden" id="editProductId">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nazwa produktu *</label>
                    <input type="text" id="editProductName" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kod EAN (opcjonalny)</label>
                    <input type="text" id="editProductEan" pattern="[0-9]{8,13}" placeholder="np. 5900008001634"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <p class="text-xs text-gray-500 mt-1">Dla produktów lokalnych bez kodu - zostaw puste</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategoria *</label>
                    <select id="editProductCategory" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Wybierz kategorię</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Podkategoria</label>
                    <input type="text" id="editProductSubcategory" placeholder="np. drób, wołowina"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zdjęcie produktu</label>
                    <input type="file" id="editProductImage" accept="image/*"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">Zostaw puste, jeśli nie chcesz zmieniać zdjęcia</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opis</label>
                    <textarea id="editProductDescription" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                </div>
                <div class="flex space-x-3">
                    <button type="submit"
                        class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Zapisz zmiany
                    </button>
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                        Anuluj
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
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
    </style>

    <script>
        const token = localStorage.getItem('auth_token');
        let allProducts = [];
        let allCategories = [];
        let currentCategoryFilter = null;
        let searchTimeout = null;

        // Check auth
        if (!token) {
            window.location.href = '{{ route('login') }}';
        }

        // Load data
        loadCategories();
        loadProducts();

        // Search handler
        document.getElementById('searchInput').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterProducts();
            }, 300);
        });

        async function loadCategories() {
            try {
                const response = await fetch('/api/categories?token=' + token, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    allCategories = data.categories;
                    displayCategoryFilters(data.categories);
                }
            } catch (error) {
                console.error('Load categories error:', error);
            }
        }

        async function loadProducts() {
            try {
                loadingState.classList.remove('hidden');
                productsList.classList.add('hidden');
                emptyState.classList.add('hidden');

                const response = await fetch('/api/products', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    if (response.status === 401) {
                        localStorage.removeItem('auth_token');
                        localStorage.removeItem('user_name');
                        window.location.href = '/login';
                        return;
                    }
                    throw new Error('Failed to load products');
                }

                const data = await response.json();

                if (data.success) {
                    allProducts = data.products;
                    filterProducts();
                }
            } catch (error) {
                console.error('Load products error:', error);
                alert('Wystąpił błąd podczas ładowania produktów');
            } finally {
                loadingState.classList.add('hidden');
            }
        }

        function displayCategoryFilters(categories) {
            const html = categories.map(cat => `
                <button 
                    onclick="filterByCategory(${cat.id})"
                    class="category-filter flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition"
                    data-category="${cat.id}"
                >
                    ${cat.icon} ${cat.name}
                </button>
            `).join('');

            document.getElementById('categoriesFilter').innerHTML = html;
        }

        function filterByCategory(categoryId) {
            currentCategoryFilter = categoryId;

            // Update active state
            document.querySelectorAll('.category-filter').forEach(btn => {
                btn.classList.remove('active');
            });

            if (categoryId === null) {
                document.querySelector('[data-category="all"]').classList.add('active');
            } else {
                document.querySelector(`[data-category="${categoryId}"]`).classList.add('active');
            }

            filterProducts();
        }

        function filterProducts() {
            const searchQuery = document.getElementById('searchInput').value.toLowerCase().trim();

            let filtered = allProducts;

            // Filter by category
            if (currentCategoryFilter !== null) {
                filtered = filtered.filter(p => p.category.id === currentCategoryFilter);
            }

            // Filter by search
            if (searchQuery) {
                filtered = filtered.filter(p =>
                    p.name.toLowerCase().includes(searchQuery) ||
                    (p.ean_code && p.ean_code.includes(searchQuery)) ||
                    (p.subcategory && p.subcategory.toLowerCase().includes(searchQuery))
                );
            }

            displayProducts(filtered);
        }

        function displayProducts(products) {
            const productsList = document.getElementById('productsList');
            const emptyState = document.getElementById('emptyState');
            const noResultsState = document.getElementById('noResultsState');

            if (products.length === 0) {
                productsList.classList.add('hidden');
                noResultsState.classList.add('hidden');

                if (allProducts.length === 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    noResultsState.classList.remove('hidden');
                }

                document.getElementById('productCount').textContent = '0 produktów';
                return;
            }

            emptyState.classList.add('hidden');
            noResultsState.classList.add('hidden');

            const html = products.map(product => `
                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition cursor-pointer" onclick="viewProduct(${product.id})">
                    <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                        ${product.image_url 
                            ? `<img src="${product.image_url}" alt="${escapeHtml(product.name)}" class="w-full h-full object-cover">`
                            : `<span class="text-5xl">${product.category.icon}</span>`
                        }
                    </div>
                    <div class="p-3">
                        <h3 class="font-semibold text-gray-900 text-sm mb-1 line-clamp-2">${escapeHtml(product.name)}</h3>
                        ${product.subcategory ? `<p class="text-xs text-gray-500 mb-1">${escapeHtml(product.subcategory)}</p>` : ''}
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs px-2 py-1 rounded" style="background-color: ${product.category.color}20; color: ${product.category.color}">
                                ${product.category.icon} ${product.category.name}
                            </span>
                        </div>
                        ${product.ean_code ? `<p class="text-xs text-gray-400 mt-2">EAN: ${product.ean_code}</p>` : ''}
                    </div>
                </div>
            `).join('');

            productsList.innerHTML = html;
            productsList.classList.remove('hidden');

            const count = products.length;
            document.getElementById('productCount').textContent =
                `${count} ${count === 1 ? 'produkt' : count < 5 ? 'produkty' : 'produktów'}`;
        }

        function viewProduct(productId) {
            editProduct(productId);
        }

        function editProduct(productId) {
            const product = allProducts.find(p => p.id === productId);
            if (!product) return;

            document.getElementById('editProductId').value = product.id;
            document.getElementById('editProductName').value = product.name;
            document.getElementById('editProductEan').value = product.ean_code || '';
            document.getElementById('editProductCategory').value = product.category.id;
            document.getElementById('editProductSubcategory').value = product.subcategory || '';
            document.getElementById('editProductDescription').value = product.description || '';

            // Populate categories if not already done
            const categorySelect = document.getElementById('editProductCategory');
            if (categorySelect.options.length === 1) {
                allCategories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = `${cat.icon} ${cat.name}`;
                    categorySelect.appendChild(option);
                });
                document.getElementById('editProductCategory').value = product.category.id;
            }

            document.getElementById('editProductModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editProductModal').classList.add('hidden');
            document.getElementById('editProductForm').reset();
        }

        // Handle edit form submit
        document.getElementById('editProductForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const productId = document.getElementById('editProductId').value;

            const formData = new FormData();
            formData.append('name', document.getElementById('editProductName').value);
            formData.append('category_id', document.getElementById('editProductCategory').value);

            const ean = document.getElementById('editProductEan').value;
            if (ean) formData.append('ean_code', ean);

            const subcategory = document.getElementById('editProductSubcategory').value;
            if (subcategory) formData.append('subcategory', subcategory);

            const description = document.getElementById('editProductDescription').value;
            if (description) formData.append('description', description);

            const imageFile = document.getElementById('editProductImage').files[0];
            if (imageFile) formData.append('image', imageFile);

            try {
                const response = await fetch(`/api/products/${productId}`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    alert('Produkt został zaktualizowany');
                    closeEditModal();
                    loadProducts();
                } else {
                    alert('Błąd: ' + (data.message || 'Nie udało się zaktualizować produktu'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Wystąpił błąd podczas aktualizacji produktu');
            }
        });

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>

</html>
