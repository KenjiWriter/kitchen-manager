<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dodaj produkty - Domowy Inwentarz</title>
    @vite(['resources/css/app.css'])
</head>

<body class="bg-gray-900 text-white min-h-screen">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <div class="bg-gray-800 px-4 py-3 flex items-center justify-between">
            <h1 class="text-lg font-bold">Dodaj do magazynu</h1>
            <a href="{{ route('scanner') }}" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>

        <!-- Main content -->
        <div class="flex-1 p-4 overflow-y-auto">
            <div class="max-w-2xl mx-auto">
                <div id="productsContainer" class="space-y-3">
                    <!-- Products will be loaded here -->
                </div>

                <!-- Group selection -->
                <div class="mt-6 bg-gray-800 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Grupa</label>
                    <select id="batchGroupSelect"
                        class="w-full px-4 py-2 bg-gray-700 text-white border border-gray-600 rounded-md focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Wybierz grupę...</option>
                    </select>
                </div>

                <!-- Global expiry date -->
                <div class="mt-3 bg-gray-800 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Data ważności (dla wszystkich)
                        <span class="text-xs text-gray-500 ml-1">(opcjonalne)</span>
                    </label>
                    <input type="date" id="globalExpiryDate"
                        class="w-full px-4 py-2 bg-gray-700 text-white border border-gray-600 rounded-md focus:border-amber-500 focus:ring-amber-500">
                    <button id="applyGlobalExpiry" class="mt-2 text-xs text-amber-400 hover:text-amber-300">
                        Zastosuj do wszystkich
                    </button>
                </div>

                <!-- Submit button -->
                <div class="mt-6 sticky bottom-0 bg-gray-900 py-4">
                    <button id="submitBatch"
                        class="w-full px-6 py-4 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-bold text-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        Dodaj wszystkie do magazynu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        let products = [];
        let userGroups = [];
        let batchCodes = [];

        // Load codes from session storage
        const codesJson = sessionStorage.getItem('batchCodes');
        if (!codesJson) {
            alert('Brak kodów do przetworzenia');
            window.location.href = '/scanner';
        }

        batchCodes = JSON.parse(codesJson);

        // Load groups
        async function loadGroups() {
            const response = await fetch('/api/groups');
            const data = await response.json();
            userGroups = data.groups;

            const select = document.getElementById('batchGroupSelect');
            userGroups.forEach(group => {
                const option = document.createElement('option');
                option.value = group.id;
                option.textContent = group.name;
                select.appendChild(option);
            });

            // Select first group by default
            if (userGroups.length > 0) {
                select.value = userGroups[0].id;
            }
        }

        // Load products for each code
        async function loadProducts() {
            const container = document.getElementById('productsContainer');

            for (const code of batchCodes) {
                const response = await fetch(`/api/products/search-ean?ean=${code}`);
                const data = await response.json();

                const product = data.product || null;
                const productCard = createProductCard(code, product);
                container.appendChild(productCard);

                products.push({
                    code,
                    product,
                    quantity: 1,
                    expiryDate: null
                });
            }
        }

        // Create product card
        function createProductCard(code, product) {
            const div = document.createElement('div');
            div.className = 'bg-gray-800 rounded-lg p-4';
            div.dataset.code = code;

            if (product) {
                div.innerHTML = `
                    <div class="flex items-start space-x-3">
                        ${product.image_url 
                            ? `<img src="${product.image_url}" class="w-16 h-16 rounded-lg object-cover flex-shrink-0" alt="${product.name}">`
                            : `<div class="w-16 h-16 rounded-lg bg-gray-700 flex items-center justify-center text-2xl flex-shrink-0">
                                    ${product.category.icon}
                                   </div>`
                        }
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-white truncate">${product.name}</h3>
                            <p class="text-xs text-gray-400 mt-0.5">${product.category.icon} ${product.category.name}</p>
                            <p class="text-xs text-gray-500 mt-0.5 font-mono">EAN: ${code}</p>
                            
                            <div class="grid grid-cols-2 gap-2 mt-3">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Ilość</label>
                                    <input type="number" min="1" value="1" 
                                        class="quantity-input w-full px-2 py-1 bg-gray-700 text-white border border-gray-600 rounded text-sm"
                                        data-code="${code}">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Data ważności</label>
                                    <input type="date" 
                                        class="expiry-input w-full px-2 py-1 bg-gray-700 text-white border border-gray-600 rounded text-sm"
                                        data-code="${code}">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                div.innerHTML = `
                    <div class="flex items-start space-x-3">
                        <div class="w-16 h-16 rounded-lg bg-gray-700 flex items-center justify-center text-2xl flex-shrink-0">
                            ❓
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-amber-400">Nieznany produkt</h3>
                            <p class="text-xs text-gray-500 mt-0.5 font-mono">EAN: ${code}</p>
                            <a href="/products/create?ean=${code}" class="inline-block mt-2 text-xs text-amber-400 hover:text-amber-300 underline">
                                Utwórz nowy produkt
                            </a>
                            <p class="text-xs text-gray-500 mt-2">Ten produkt zostanie pominięty</p>
                        </div>
                    </div>
                `;
                div.classList.add('opacity-50');
            }

            return div;
        }

        // Update quantity
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('quantity-input')) {
                const code = e.target.dataset.code;
                const product = products.find(p => p.code === code);
                if (product) {
                    product.quantity = parseInt(e.target.value) || 1;
                }
            }
        });

        // Update expiry date
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('expiry-input')) {
                const code = e.target.dataset.code;
                const product = products.find(p => p.code === code);
                if (product) {
                    product.expiryDate = e.target.value || null;
                }
            }
        });

        // Apply global expiry date
        document.getElementById('applyGlobalExpiry').addEventListener('click', () => {
            const globalDate = document.getElementById('globalExpiryDate').value;
            if (!globalDate) {
                alert('Wybierz datę');
                return;
            }

            document.querySelectorAll('.expiry-input').forEach(input => {
                input.value = globalDate;
                const code = input.dataset.code;
                const product = products.find(p => p.code === code);
                if (product) {
                    product.expiryDate = globalDate;
                }
            });
        });

        // Submit batch
        document.getElementById('submitBatch').addEventListener('click', async () => {
            const groupId = document.getElementById('batchGroupSelect').value;
            if (!groupId) {
                alert('Wybierz grupę');
                return;
            }

            const validProducts = products.filter(p => p.product !== null);
            if (validProducts.length === 0) {
                alert('Brak produktów do dodania');
                return;
            }

            const button = document.getElementById('submitBatch');
            button.disabled = true;
            button.textContent = 'Dodawanie...';

            let added = 0;
            let failed = 0;

            for (const item of validProducts) {
                const data = {
                    product_id: item.product.id,
                    user_group_id: groupId,
                    quantity: item.quantity,
                    expiry_date: item.expiryDate
                };

                try {
                    const response = await fetch('/api/pantry', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                    });

                    if (response.ok) {
                        added++;
                    } else {
                        failed++;
                    }
                } catch (error) {
                    failed++;
                }
            }

            sessionStorage.removeItem('batchCodes');

            if (failed === 0) {
                alert(`Dodano ${added} produktów do magazynu!`);
                window.location.href = '/pantry';
            } else {
                alert(`Dodano ${added} produktów. Nie udało się dodać ${failed}.`);
                button.disabled = false;
                button.textContent = 'Dodaj wszystkie do magazynu';
            }
        });

        // Initialize
        loadGroups();
        loadProducts();
    </script>
</body>

</html>
