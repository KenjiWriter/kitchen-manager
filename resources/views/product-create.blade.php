<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Dodaj Produkt - Inwentarz Kuchenny</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('products') }}" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </a>
                    <h1 class="text-lg font-bold text-gray-900">Dodaj Produkt</h1>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-2xl mx-auto px-4 py-6">
        <form id="productForm" class="bg-white rounded-lg shadow p-6 space-y-6">
            <!-- Image Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Zdjęcie produktu
                </label>
                <div class="flex items-center space-x-4">
                    <div id="imagePreview"
                        class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                        <span class="text-4xl text-gray-400">📦</span>
                    </div>
                    <div class="flex-1">
                        <input type="file" id="imageInput" accept="image/jpeg,image/jpg,image/png,image/webp"
                            class="hidden">
                        <button type="button" onclick="document.getElementById('imageInput').click()"
                            class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition">
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            Wybierz zdjęcie
                        </button>
                        <p class="text-xs text-gray-500 mt-2">JPG, PNG lub WEBP, max 2MB</p>
                    </div>
                </div>
            </div>

            <!-- Product Name -->
            <div>
                <label for="productName" class="block text-sm font-medium text-gray-700 mb-2">
                    Nazwa produktu <span class="text-red-500">*</span>
                </label>
                <input type="text" id="productName" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                    placeholder="np. Mleko 2% Łaciate">
            </div>

            <!-- EAN Code -->
            <div>
                <label for="eanCode" class="block text-sm font-medium text-gray-700 mb-2">
                    Kod EAN (opcjonalny)
                </label>
                <input type="text" id="eanCode" maxlength="13" pattern="[0-9]{8,13}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                    placeholder="np. 5900512010014">
                <p class="text-xs text-gray-500 mt-1">Dla produktów bez kodu (warzywa, owoce, jajka) - zostaw puste</p>
            </div>

            <!-- Category -->
            <div>
                <label for="categoryId" class="block text-sm font-medium text-gray-700 mb-2">
                    Kategoria <span class="text-red-500">*</span>
                </label>
                <select id="categoryId" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Wybierz kategorię...</option>
                </select>
            </div>

            <!-- Subcategory -->
            <div>
                <label for="subcategory" class="block text-sm font-medium text-gray-700 mb-2">
                    Podkategoria
                </label>
                <input type="text" id="subcategory" list="subcategoryList"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                    placeholder="np. wołowina, drób, warzywa korzeninowe">
                <datalist id="subcategoryList"></datalist>
                <p class="text-xs text-gray-500 mt-1">Np. dla kategorii "Mięso" → "drób", "wołowina"</p>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Opis
                </label>
                <textarea id="description" rows="3" maxlength="1000"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-none"
                    placeholder="Dodatkowe informacje o produkcie..."></textarea>
            </div>

            <!-- Submit Buttons -->
            <div class="flex space-x-3 pt-4">
                <a href="{{ route('products') }}"
                    class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition text-center">
                    Anuluj
                </a>
                <button type="submit" id="submitBtn"
                    class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Dodaj produkt
                </button>
            </div>
        </form>
    </main>

    <script>
        const token = localStorage.getItem('auth_token');
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');
        const categorySelect = document.getElementById('categoryId');
        const subcategoryInput = document.getElementById('subcategory');
        const productForm = document.getElementById('productForm');
        let selectedImage = null;

        // Check auth
        if (!token) {
            window.location.href = '{{ route('login') }}';
        }

        // Load categories
        loadCategories();

        // Image preview
        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Plik jest za duży. Maksymalny rozmiar to 2MB.');
                    imageInput.value = '';
                    return;
                }

                selectedImage = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreview.innerHTML =
                        `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                };
                reader.readAsDataURL(file);
            }
        });

        // Category change handler - load subcategories
        categorySelect.addEventListener('change', async () => {
            const categoryId = categorySelect.value;
            if (!categoryId) return;

            try {
                const response = await fetch(`/api/categories?token=${token}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();

                if (data.success) {
                    const category = data.categories.find(c => c.id == categoryId);
                    if (category && category.subcategories.length > 0) {
                        const datalist = document.getElementById('subcategoryList');
                        datalist.innerHTML = category.subcategories.map(sub =>
                            `<option value="${sub}">`
                        ).join('');
                    }
                }
            } catch (error) {
                console.error('Load subcategories error:', error);
            }
        });

        // Form submission
        productForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Dodawanie...';

            try {
                const formData = new FormData();
                formData.append('token', token);
                formData.append('name', document.getElementById('productName').value);
                formData.append('category_id', document.getElementById('categoryId').value);

                const eanCode = document.getElementById('eanCode').value;
                if (eanCode) {
                    if (eanCode.length !== 13 || !/^\d+$/.test(eanCode)) {
                        alert('Kod EAN musi mieć dokładnie 13 cyfr');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Dodaj produkt';
                        return;
                    }
                    formData.append('ean_code', eanCode);
                }

                const subcategory = document.getElementById('subcategory').value;
                if (subcategory) {
                    formData.append('subcategory', subcategory);
                }

                const description = document.getElementById('description').value;
                if (description) {
                    formData.append('description', description);
                }

                if (selectedImage) {
                    formData.append('image', selectedImage);
                }

                const response = await fetch('/api/products', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = '{{ route('products') }}';
                } else {
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join('\n');
                        alert('Błędy walidacji:\n' + errorMessages);
                    } else {
                        alert(data.message || 'Wystąpił błąd podczas dodawania produktu');
                    }
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Dodaj produkt';
                }
            } catch (error) {
                console.error('Add product error:', error);
                alert('Wystąpił błąd połączenia');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Dodaj produkt';
            }
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
                    const options = data.categories.map(cat =>
                        `<option value="${cat.id}">${cat.icon} ${cat.name}</option>`
                    ).join('');

                    categorySelect.innerHTML += options;
                }
            } catch (error) {
                console.error('Load categories error:', error);
                alert('Wystąpił błąd podczas ładowania kategorii');
            }
        }
    </script>
</body>

</html>
