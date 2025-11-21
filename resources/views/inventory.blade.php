<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inwentarz - Kitchen Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-900 text-white min-h-screen pb-24">
    <div class="max-w-4xl mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">📦 Inwentarz</h1>
            <a href="/" class="text-amber-500 hover:text-amber-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>

        <!-- Scan EAN Section -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">🔍 Skanuj kod EAN</h2>
            <p class="text-sm text-gray-400 mb-4">Zeskanuj lub wpisz kod EAN, aby zobaczyć produkt i jego historię
                skanowań</p>

            <div class="flex flex-col sm:flex-row gap-3">
                <input type="text" id="eanInput" placeholder="Wpisz kod EAN (8-13 cyfr)" pattern="[0-9]{8,13}"
                    class="flex-1 px-4 py-3 bg-gray-700 text-white border border-gray-600 rounded-md focus:border-amber-500 focus:ring-amber-500">
                <button id="scanBtn"
                    class="px-6 py-3 bg-amber-600 text-white rounded-md hover:bg-amber-700 font-medium">
                    Szukaj
                </button>
            </div>

            <div class="mt-3 text-center">
                <button id="cameraBtn" class="text-blue-400 text-sm underline hover:text-blue-300">
                    Lub użyj kamery do skanowania
                </button>
            </div>
        </div>

        <!-- Product Info Card (hidden by default) -->
        <div id="productCard" class="hidden bg-gray-800 rounded-lg p-6 mb-6">
            <div class="flex items-start space-x-4 mb-4">
                <div id="productImage"
                    class="w-20 h-20 rounded-lg bg-gray-700 flex items-center justify-center text-3xl flex-shrink-0">

                </div>
                <div class="flex-1">
                    <h3 id="productName" class="text-xl font-bold mb-1"></h3>
                    <p id="productCategory" class="text-sm text-gray-400 mb-2"></p>
                    <p id="productEan" class="text-xs font-mono text-gray-500"></p>
                </div>
            </div>

            <div class="flex gap-3">
                <button id="recordScanBtn"
                    class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium">
                    ✓ Zapisz skanowanie
                </button>
                <button id="viewPantryBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Magazyn
                </button>
            </div>
        </div>

        <!-- Scan History Section -->
        <div id="historySection" class="hidden">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">📜 Historia skanowań</h2>
                <span id="totalScans" class="text-sm text-gray-400"></span>
            </div>

            <div id="historyList" class="space-y-3">
                <!-- History items will be inserted here -->
            </div>

            <div id="noHistory" class="hidden text-center py-8 text-gray-500">
                <p>Brak historii skanowań dla tego produktu</p>
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="text-center py-12">
            <div class="text-6xl mb-4">🔍</div>
            <h3 class="text-xl font-semibold mb-2">Wyszukaj produkt</h3>
            <p class="text-gray-400">Wpisz lub zeskanuj kod EAN, aby zobaczyć informacje o produkcie</p>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-gray-800 border-t border-gray-700 safe-area-bottom">
        <div class="max-w-md mx-auto px-4 py-3 flex justify-around items-center">
            <a href="/pantry" class="flex flex-col items-center text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span class="text-xs mt-1">Magazyn</span>
            </a>

            <a href="/scanner" class="flex flex-col items-center text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-xs mt-1">Skanuj</span>
            </a>

            <a href="/products" class="flex flex-col items-center text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="text-xs mt-1">Produkty</span>
            </a>
        </div>
    </nav>

    <script>
        let currentProduct = null;

        // Search by EAN
        async function searchByEan(ean) {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                alert('Musisz być zalogowany');
                window.location.href = '/login';
                return;
            }

            try {
                const response = await fetch(`/api/scan-history/by-ean?ean=${encodeURIComponent(ean)}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    const error = await response.json();
                    alert(error.message || 'Produkt nie został znaleziony');
                    return;
                }

                const data = await response.json();
                displayProduct(data.product, data.history, data.total_scans);
            } catch (error) {
                console.error('Error searching product:', error);
                alert('Błąd podczas wyszukiwania produktu');
            }
        }

        // Display product and history
        function displayProduct(product, history, totalScans) {
            currentProduct = product;

            // Hide empty state
            document.getElementById('emptyState').classList.add('hidden');

            // Show product card
            const productCard = document.getElementById('productCard');
            productCard.classList.remove('hidden');

            // Set product info
            const productImage = document.getElementById('productImage');
            if (product.image_url) {
                productImage.innerHTML =
                    `<img src="${product.image_url}" class="w-full h-full object-cover rounded-lg" alt="${product.name}">`;
            } else {
                productImage.innerHTML = product.category.icon;
            }

            document.getElementById('productName').textContent = product.name;
            document.getElementById('productCategory').textContent = `${product.category.icon} ${product.category.name}`;
            document.getElementById('productEan').textContent = `EAN: ${product.ean_code}`;

            // Show history section
            const historySection = document.getElementById('historySection');
            historySection.classList.remove('hidden');

            document.getElementById('totalScans').textContent = `${totalScans} skanowań`;

            // Display history
            const historyList = document.getElementById('historyList');
            const noHistory = document.getElementById('noHistory');

            if (history.length === 0) {
                historyList.innerHTML = '';
                noHistory.classList.remove('hidden');
            } else {
                noHistory.classList.add('hidden');
                historyList.innerHTML = history.map(scan => `
                    <div class="bg-gray-700 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="font-medium">${scan.scanner.name}</span>
                                </div>
                                <div class="flex items-center space-x-2 text-sm text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>${scan.scanned_at_human}</span>
                                </div>
                                ${scan.location ? `<div class="text-xs text-gray-500 mt-1">📍 ${scan.location}</div>` : ''}
                                ${scan.notes ? `<div class="text-sm text-gray-300 mt-2">${scan.notes}</div>` : ''}
                            </div>
                            <div class="text-xs text-gray-500">
                                ${scan.scanned_at}
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Record scan
        async function recordScan() {
            if (!currentProduct) return;

            const token = localStorage.getItem('auth_token');
            if (!token) {
                alert('Musisz być zalogowany');
                window.location.href = '/login';
                return;
            }

            try {
                const response = await fetch('/api/scan-history/record', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        ean_code: currentProduct.ean_code,
                        location: 'inventory',
                        notes: null
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to record scan');
                }

                // Refresh history
                searchByEan(currentProduct.ean_code);

                // Show notification
                const notification = document.createElement('div');
                notification.className =
                    'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                notification.textContent = 'Skanowanie zapisane!';
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 2000);
            } catch (error) {
                console.error('Error recording scan:', error);
                alert('Błąd podczas zapisywania skanowania');
            }
        }

        // Event listeners
        document.getElementById('scanBtn').addEventListener('click', () => {
            const ean = document.getElementById('eanInput').value.trim();
            if (ean.length < 8 || ean.length > 13) {
                alert('Kod EAN musi mieć 8-13 cyfr');
                return;
            }
            searchByEan(ean);
        });

        document.getElementById('eanInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                document.getElementById('scanBtn').click();
            }
        });

        document.getElementById('cameraBtn').addEventListener('click', () => {
            window.location.href = '/scanner';
        });

        document.getElementById('recordScanBtn').addEventListener('click', recordScan);

        document.getElementById('viewPantryBtn').addEventListener('click', () => {
            window.location.href = '/pantry';
        });
    </script>

    <style>
        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }
    </style>
</body>

</html>
