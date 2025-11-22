<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Listy zakupów - Inwentarz Kuchenny</title>
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
                        <h1 class="text-lg font-bold text-gray-900">Listy zakupów</h1>
                        <p class="text-sm text-gray-500" id="listCount">Ładowanie...</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Shopping Lists -->
    <div class="max-w-7xl mx-auto px-4 py-4">
        <div id="loadingState" class="text-center py-8">
            <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <p class="text-gray-500">Ładowanie list...</p>
        </div>

        <div id="emptyState" class="hidden text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Brak list zakupów</h3>
            <p class="text-gray-500">Listy zakupów możesz tworzyć z przepisów</p>
        </div>

        <div id="shoppingLists" class="hidden space-y-4"></div>
    </div>

    <!-- List Detail Modal -->
    <div id="listModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto">
        <div class="min-h-screen px-4 flex items-center justify-center">
            <div class="bg-white w-full max-w-2xl rounded-lg shadow-xl my-8">
                <div
                    class="sticky top-0 bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between rounded-t-lg">
                    <h2 class="text-lg font-bold" id="listModalTitle"></h2>
                    <button onclick="closeListModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <div id="listModalContent" class="p-4"></div>
            </div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('auth_token');
        let shoppingLists = [];
        let currentList = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadShoppingLists();
        });

        async function loadShoppingLists() {
            try {
                const response = await fetch('/api/shopping-lists', {
                    headers: {
                        'X-Auth-Token': token,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                shoppingLists = data.shopping_lists || [];

                document.getElementById('loadingState').classList.add('hidden');

                if (shoppingLists.length === 0) {
                    document.getElementById('emptyState').classList.remove('hidden');
                    document.getElementById('listCount').textContent = 'Brak list';
                } else {
                    document.getElementById('shoppingLists').classList.remove('hidden');
                    document.getElementById('listCount').textContent = `${shoppingLists.length} list`;
                    renderShoppingLists();
                }
            } catch (error) {
                console.error('Load shopping lists error:', error);
            }
        }

        function renderShoppingLists() {
            const container = document.getElementById('shoppingLists');

            container.innerHTML = shoppingLists.map(list => {
                const checkedCount = list.items.filter(item => item.is_checked).length;
                const totalCount = list.items.length;
                const progress = totalCount > 0 ? Math.round((checkedCount / totalCount) * 100) : 0;

                return `
                    <div class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-lg transition"
                        onclick="showListDetail(${list.id})">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg">${list.name}</h3>
                                ${list.notes ? `<p class="text-sm text-gray-500 mt-1">${list.notes}</p>` : ''}
                            </div>
                            <button onclick="event.stopPropagation(); deleteList(${list.id})" 
                                class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-gray-600">${checkedCount} / ${totalCount} pozycji</span>
                            <span class="font-medium">${progress}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full transition-all" 
                                style="width: ${progress}%"></div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function showListDetail(listId) {
            currentList = shoppingLists.find(l => l.id === listId);
            if (!currentList) return;

            document.getElementById('listModalTitle').textContent = currentList.name;
            renderListDetail();
            document.getElementById('listModal').classList.remove('hidden');
        }

        function renderListDetail() {
            if (!currentList) return;

            const checkedCount = currentList.items.filter(item => item.is_checked).length;
            const totalCount = currentList.items.length;

            document.getElementById('listModalContent').innerHTML = `
                ${currentList.notes ? `
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <p class="text-sm text-blue-800">${currentList.notes}</p>
                        </div>
                    ` : ''}

                <div class="mb-4">
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="font-medium">Postęp zakupów</span>
                        <span>${checkedCount} / ${totalCount}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-green-500 h-3 rounded-full transition-all" 
                            style="width: ${totalCount > 0 ? Math.round((checkedCount / totalCount) * 100) : 0}%"></div>
                    </div>
                </div>

                <div class="space-y-2">
                    ${currentList.items.map(item => `
                            <div class="flex items-center space-x-3 p-3 rounded-lg border ${item.is_checked ? 'bg-gray-50 border-gray-200' : 'bg-white border-gray-300'}">
                                <input type="checkbox" 
                                    ${item.is_checked ? 'checked' : ''}
                                    onchange="toggleItem(${currentList.id}, ${item.id})"
                                    class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                                <div class="flex-1">
                                    <p class="font-medium ${item.is_checked ? 'line-through text-gray-500' : 'text-gray-900'}">
                                        ${item.name}
                                    </p>
                                    <p class="text-sm text-gray-500">${item.quantity}</p>
                                </div>
                            </div>
                        `).join('')}
                </div>

                ${totalCount === checkedCount && totalCount > 0 ? `
                        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg text-center">
                            <p class="text-green-800 font-medium">🎉 Wszystkie zakupy zrobione!</p>
                        </div>
                    ` : ''}
            `;
        }

        async function toggleItem(listId, itemId) {
            try {
                const response = await fetch(`/api/shopping-lists/${listId}/items/${itemId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-Auth-Token': token,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Update local data
                    const list = shoppingLists.find(l => l.id === listId);
                    const item = list.items.find(i => i.id === itemId);
                    item.is_checked = data.is_checked;

                    // Re-render
                    if (currentList && currentList.id === listId) {
                        currentList = list;
                        renderListDetail();
                    }
                    renderShoppingLists();
                }
            } catch (error) {
                console.error('Toggle item error:', error);
            }
        }

        async function deleteList(listId) {
            if (!confirm('Czy na pewno chcesz usunąć tę listę zakupów?')) return;

            try {
                const response = await fetch(`/api/shopping-lists/${listId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Auth-Token': token,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    shoppingLists = shoppingLists.filter(l => l.id !== listId);

                    if (currentList && currentList.id === listId) {
                        closeListModal();
                    }

                    if (shoppingLists.length === 0) {
                        document.getElementById('shoppingLists').classList.add('hidden');
                        document.getElementById('emptyState').classList.remove('hidden');
                    } else {
                        renderShoppingLists();
                    }

                    document.getElementById('listCount').textContent = `${shoppingLists.length} list`;
                }
            } catch (error) {
                console.error('Delete list error:', error);
            }
        }

        function closeListModal() {
            document.getElementById('listModal').classList.add('hidden');
            currentList = null;
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
                <a href="{{ route('recipes') }}"
                    class="flex flex-col items-center py-3 text-gray-600 hover:text-blue-600 transition">
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
