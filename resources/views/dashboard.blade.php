<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Dashboard - Inwentarz Kuchenny</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">🍳</span>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Moja Kuchnia</h1>
                        <p class="text-sm text-gray-500" id="userName">Ładowanie...</p>
                    </div>
                </div>
                <button id="logoutBtn" class="text-gray-600 hover:text-gray-900 transition" title="Wyloguj">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6 pb-24">
        <!-- Quick Stats -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="bg-white rounded-lg shadow p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600">W magazynie</p>
                        <p id="pantryCount" class="text-xl font-bold text-gray-900">0</p>
                    </div>
                    <span class="text-2xl">🏠</span>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600">Produkty</p>
                        <p id="productsCount" class="text-xl font-bold text-gray-900">0</p>
                    </div>
                    <span class="text-2xl">📦</span>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-orange-600">Wkrótce</p>
                        <p id="expiringCount" class="text-xl font-bold text-orange-600">0</p>
                    </div>
                    <span class="text-2xl">⚠️</span>
                </div>
            </div>
            <a href="{{ route('groups') }}"
                class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow p-3 hover:from-blue-600 hover:to-blue-700 transition-all active:scale-95">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-blue-100">Zarządzaj</p>
                        <p class="text-xl font-bold text-white">Grupy</p>
                    </div>
                    <span class="text-2xl">👥</span>
                </div>
            </a>
        </div>

        <!-- Recent Items -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Ostatnio dodane do magazynu</h2>
            </div>
            <div id="recentItemsContainer">
                <div class="p-8 text-center text-gray-500">
                    <span class="text-5xl mb-4 block">📭</span>
                    <p>Ładowanie...</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg"
        style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="grid grid-cols-3 gap-0 max-w-md mx-auto">
            <a href="{{ route('pantry') }}"
                class="flex flex-col items-center py-3 text-gray-600 hover:text-green-600 transition">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-xs font-medium">Magazyn</span>
            </a>
            <a href="{{ route('scanner') }}" class="flex flex-col items-center -mt-6 relative">
                <div class="bg-amber-600 rounded-full p-4 shadow-xl hover:bg-amber-700 active:scale-95 transition">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <span class="text-xs font-bold text-amber-600 mt-1">Skanuj</span>
            </a>
            <a href="{{ route('products') }}"
                class="flex flex-col items-center py-3 text-gray-600 hover:text-amber-600 transition">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span class="text-xs font-medium">Produkty</span>
            </a>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('auth_token');
            const userName = localStorage.getItem('user_name');
            const logoutBtn = document.getElementById('logoutBtn');

            // Display user name
            if (userName) {
                document.getElementById('userName').textContent = `Witaj, ${userName}!`;
            }

            // Verify authentication
            if (!token) {
                window.location.href = '{{ route('login') }}';
                return;
            }

            // Logout handler
            logoutBtn.addEventListener('click', async function() {
                if (!confirm('Czy na pewno chcesz się wylogować?')) {
                    return;
                }

                try {
                    await fetch('{{ route('logout') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            token
                        })
                    });

                    // Clear localStorage
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user_id');
                    localStorage.removeItem('user_name');

                    // Redirect to login
                    window.location.href = '{{ route('login') }}';
                } catch (error) {
                    console.error('Logout error:', error);
                    alert('Wystąpił błąd podczas wylogowywania');
                }
            });

            // Load statistics
            async function loadStats() {
                try {
                    // Pantry statistics
                    const pantryResponse = await fetch('/api/pantry/statistics');
                    const pantryData = await pantryResponse.json();

                    document.getElementById('pantryCount').textContent = pantryData.statistics.total_items;
                    document.getElementById('expiringCount').textContent = pantryData.statistics.expiring_soon;

                    // Products count
                    const productsResponse = await fetch('/api/products');
                    const productsData = await productsResponse.json();
                    document.getElementById('productsCount').textContent = productsData.products.length;
                } catch (error) {
                    console.error('Error loading stats:', error);
                }
            }

            // Load recent pantry items
            async function loadRecentItems() {
                try {
                    const response = await fetch('/api/pantry');
                    const data = await response.json();
                    const container = document.getElementById('recentItemsContainer');

                    if (!data.items || data.items.length === 0) {
                        container.innerHTML = `
                            <div class="p-8 text-center text-gray-500">
                                <span class="text-5xl mb-4 block">📭</span>
                                <p>Brak produktów w magazynie</p>
                                <p class="text-sm mt-2">Zeskanuj kod kreskowy, aby dodać pierwszy produkt</p>
                            </div>
                        `;
                        return;
                    }

                    // Sort by created_at descending and take first 5
                    const recentItems = data.items
                        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
                        .slice(0, 5);

                    container.innerHTML = recentItems.map(item => {
                        const expiryDate = item.expiry_date ? new Date(item.expiry_date) : null;
                        const daysUntilExpiry = item.days_until_expiry;
                        let expiryBadge = '';

                        if (expiryDate) {
                            if (daysUntilExpiry < 0) {
                                expiryBadge =
                                    '<span class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded-full">Przeterminowane</span>';
                            } else if (daysUntilExpiry <= 3) {
                                expiryBadge =
                                    `<span class="text-xs px-2 py-1 bg-orange-100 text-orange-700 rounded-full">${daysUntilExpiry} dni</span>`;
                            } else {
                                expiryBadge =
                                    `<span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-full">${daysUntilExpiry} dni</span>`;
                            }
                        }

                        return `
                            <div class="p-4 border-b border-gray-100 hover:bg-gray-50">
                                <div class="flex items-center space-x-3">
                                    ${item.product.image_url 
                                        ? `<img src="${item.product.image_url}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0" alt="${item.product.name}">`
                                        : `<div class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center text-xl flex-shrink-0">
                                                ${item.product.category.icon}
                                               </div>`
                                    }
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-medium text-gray-900 truncate">${item.product.name}</h3>
                                        <p class="text-sm text-gray-500">${item.product.category.icon} ${item.product.category.name} • Ilość: ${item.quantity}</p>
                                    </div>
                                    ${expiryBadge}
                                </div>
                            </div>
                        `;
                    }).join('');
                } catch (error) {
                    console.error('Error loading recent items:', error);
                    document.getElementById('recentItemsContainer').innerHTML = `
                        <div class="p-8 text-center text-gray-500">
                            <p>Nie udało się załadować produktów</p>
                        </div>
                    `;
                }
            }

            loadStats();
            loadRecentItems();
        });
    </script>
</body>

</html>
