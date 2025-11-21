<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stan Kuchenny - Domowy Inwentarz</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/scanner.js'])
</head>

<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Stan Kuchenny</h1>
                <p class="text-sm text-gray-600 mt-1">Zarządzaj produktami w Twojej kuchni</p>
            </div>
            <div class="flex items-center space-x-3">
                <button id="openSearchModal"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Szukaj po EAN</span>
                </button>
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Alert for expiring items -->
        <div id="expiryAlert" class="hidden mb-6 bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm text-red-800">
                        <span id="expiryCount" class="font-bold">0</span> produktów z krótkim terminem ważności (≤ 3
                        dni)
                    </p>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-xs text-gray-600 uppercase tracking-wide">Wszystkie</p>
                <p id="totalItems" class="text-2xl font-bold text-gray-900 mt-1">0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-xs text-orange-600 uppercase tracking-wide">Wkrótce</p>
                <p id="expiringSoonItems" class="text-2xl font-bold text-orange-600 mt-1">0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-xs text-red-600 uppercase tracking-wide">Przeterminowane</p>
                <p id="expiredItems" class="text-2xl font-bold text-red-600 mt-1">0</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <input type="text" id="filterSearch" placeholder="Szukaj po nazwie produktu..."
                    class="rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <input type="text" id="filterLocation" placeholder="Filtruj po lokalizacji..."
                    class="rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <select id="filterGroup"
                    class="rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Wszystkie grupy</option>
                </select>
                <select id="filterExpiry"
                    class="rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Wszystkie terminy</option>
                    <option value="expiring_soon">Wkrótce (≤ 3 dni)</option>
                    <option value="expired">Przeterminowane</option>
                </select>
            </div>
        </div>

        <!-- Items list -->
        <div id="pantryItems" class="space-y-4">
            <!-- Items will be loaded here -->
        </div>

        <!-- Empty state -->
        <div id="emptyState" class="hidden text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Brak produktów w magazynie</h3>
            <p class="mt-1 text-sm text-gray-500">Zeskanuj lub dodaj produkty manualnie</p>
        </div>

        <!-- Loading state -->
        <div id="loadingState" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-amber-600"></div>
            <p class="mt-2 text-sm text-gray-600">Ładowanie...</p>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div id="editItemModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-xl font-bold">Edytuj produkt w magazynie</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="editItemForm" class="space-y-4">
                <input type="hidden" id="editItemId">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nazwa produktu</label>
                    <input type="text" id="editItemName" disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ilość</label>
                    <input type="number" id="editItemQuantity" min="1" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grupa magazynowa</label>
                    <select id="editItemGroup" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Mój prywatny magazyn</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Termin ważności</label>
                    <input type="date" id="editItemExpiry"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokalizacja</label>
                    <input type="text" id="editItemLocation" placeholder="np. lodówka, zamrażarka"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notatki</label>
                    <textarea id="editItemNotes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                </div>
                <div class="flex space-x-3">
                    <button type="submit"
                        class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700">
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

    <!-- Consume Quantity Modal -->
    <div id="consumeModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-sm w-full p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-xl font-bold">Ile zużyłeś?</h3>
                <button onclick="closeConsumeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="consumeForm">
                <input type="hidden" id="consumeItemId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produkt</label>
                    <p id="consumeItemName" class="text-base font-semibold"></p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dostępna ilość: <span
                            id="consumeItemAvailable" class="font-bold"></span></label>
                    <input type="number" id="consumeQuantity" min="1" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Ile sztuk zużyłeś?">
                </div>
                <div class="flex space-x-3">
                    <button type="submit"
                        class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        Potwierdź
                    </button>
                    <button type="button" onclick="closeConsumeModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                        Anuluj
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Search EAN Modal -->
    <div id="searchEanModal"
        class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-2xl w-full text-gray-900 max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-xl font-bold">🔍 Szukaj produktu po kodzie EAN</h3>
                    <button id="closeSearchModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="searchEanForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kod EAN *</label>
                        <input type="text" id="eanCodeInput" placeholder="np. 5900008001634"
                            pattern="[0-9]{8,13}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Zdjęcie kodu kreskowego (opcjonalne)
                        </label>
                        <input type="file" id="eanImageInput" accept="image/*" capture="environment"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-xs text-gray-500">Możesz zrobić zdjęcie lub wybrać z galerii</p>
                    </div>

                    <!-- Image Preview -->
                    <div id="imagePreview" class="hidden">
                        <img id="previewImage" class="w-full rounded-lg max-h-64 object-contain bg-gray-100"
                            alt="Preview">
                    </div>

                    <div class="flex space-x-3">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 font-medium">
                            Szukaj
                        </button>
                        <button type="button" id="cancelSearch"
                            class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 font-medium">
                            Anuluj
                        </button>
                    </div>
                </form>

                <!-- Product History Section -->
                <div id="productHistory" class="hidden mt-6 pt-6 border-t border-gray-200">
                    <h4 class="text-lg font-bold mb-4">Historia skanowań</h4>
                    <div id="productHistoryContent"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let allItems = [];
        let userGroups = [];

        // Load user groups
        async function loadGroups() {
            const response = await fetch('/api/groups');
            const data = await response.json();
            userGroups = data.groups;

            const filterGroup = document.getElementById('filterGroup');
            const editGroup = document.getElementById('editItemGroup');

            userGroups.forEach(group => {
                const option = document.createElement('option');
                option.value = group.id;
                option.textContent = group.name;
                filterGroup.appendChild(option);

                // Add to edit modal
                const editOption = document.createElement('option');
                editOption.value = group.id;
                editOption.textContent = group.name;
                editGroup.appendChild(editOption);
            });
        }

        // Load statistics
        async function loadStatistics() {
            const groupId = document.getElementById('filterGroup').value;
            const url = groupId ? `/api/pantry/statistics?group_id=${groupId}` : '/api/pantry/statistics';

            const response = await fetch(url);
            const data = await response.json();

            document.getElementById('totalItems').textContent = data.statistics.total_items;
            document.getElementById('expiringSoonItems').textContent = data.statistics.expiring_soon;
            document.getElementById('expiredItems').textContent = data.statistics.expired;

            // Show expiry alert if needed
            if (data.statistics.expiring_soon > 0) {
                document.getElementById('expiryCount').textContent = data.statistics.expiring_soon;
                document.getElementById('expiryAlert').classList.remove('hidden');
            } else {
                document.getElementById('expiryAlert').classList.add('hidden');
            }
        }

        // Load pantry items
        async function loadItems() {
            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('emptyState').classList.add('hidden');

            const params = new URLSearchParams();
            const groupId = document.getElementById('filterGroup').value;
            const expiryStatus = document.getElementById('filterExpiry').value;
            const location = document.getElementById('filterLocation').value;
            const search = document.getElementById('filterSearch').value;

            if (groupId) params.append('group_id', groupId);
            if (expiryStatus) params.append('expiry_status', expiryStatus);
            if (location) params.append('location', location);
            if (search) params.append('search', search);

            const response = await fetch(`/api/pantry?${params.toString()}`);
            const data = await response.json();
            allItems = data.items;

            document.getElementById('loadingState').classList.add('hidden');

            renderItems();
            loadStatistics();
        }

        // Render items
        function renderItems() {
            const container = document.getElementById('pantryItems');
            container.innerHTML = '';

            if (allItems.length === 0) {
                document.getElementById('emptyState').classList.remove('hidden');
                return;
            }

            allItems.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'bg-white rounded-lg shadow-md p-4 flex items-start space-x-4';

                // Status color
                let statusColor = 'bg-green-100 border-green-500';
                if (item.is_expired) {
                    statusColor = 'bg-red-100 border-red-500';
                } else if (item.is_expiring_soon) {
                    statusColor = 'bg-orange-100 border-orange-500';
                }

                itemDiv.innerHTML = `
                    <div class="flex-shrink-0">
                        ${item.product.image_url 
                            ? `<img src="${item.product.image_url}" class="w-16 h-16 rounded-lg object-cover" alt="${item.product.name}">`
                            : `<div class="w-16 h-16 rounded-lg ${statusColor} border-2 flex items-center justify-center text-3xl">
                                            ${item.product.category.icon}
                                           </div>`
                        }
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-gray-900">${item.product.name}</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    ${item.product.category.icon} ${item.product.category.name}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button onclick="editItem(${item.id})" class="text-blue-600 hover:text-blue-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button onclick="consumeItem(${item.id})" class="text-green-600 hover:text-green-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                                <button onclick="deleteItem(${item.id})" class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-600">Ilość:</span>
                                <span class="font-semibold ml-1">${item.quantity}</span>
                            </div>
                            ${item.location ? `
                                        <div>
                                            <span class="text-gray-600">Lokalizacja:</span>
                                            <span class="font-semibold ml-1">${item.location}</span>
                                        </div>
                                        ` : ''}
                            ${item.expiry_date ? `
                                        <div class="col-span-2">
                                            <span class="text-gray-600">Termin ważności:</span>
                                            <span class="font-semibold ml-1 ${item.is_expired ? 'text-red-600' : item.is_expiring_soon ? 'text-orange-600' : 'text-green-600'}">
                                                ${new Date(item.expiry_date).toLocaleDateString('pl-PL')}
                                                ${item.days_until_expiry !== null ? `(${item.days_until_expiry} dni)` : ''}
                                            </span>
                                        </div>
                                        ` : ''}
                            ${item.group ? `
                                        <div class="col-span-2">
                                            <span class="text-gray-600">Grupa:</span>
                                            <span class="font-semibold ml-1">${item.group.name}</span>
                                        </div>
                                        ` : ''}
                            ${item.notes ? `
                                        <div class="col-span-2">
                                            <p class="text-gray-600 text-xs italic">${item.notes}</p>
                                        </div>
                                        ` : ''}
                        </div>
                    </div>
                `;

                container.appendChild(itemDiv);
            });
        }

        // Edit item
        function editItem(id) {
            const item = allItems.find(i => i.id === id);
            if (!item) return;

            document.getElementById('editItemId').value = item.id;
            document.getElementById('editItemName').value = item.product.name;
            document.getElementById('editItemQuantity').value = item.quantity;
            document.getElementById('editItemGroup').value = item.group ? item.group.id : '';
            document.getElementById('editItemExpiry').value = item.expiry_date || '';
            document.getElementById('editItemLocation').value = item.location || '';
            document.getElementById('editItemNotes').value = item.notes || '';
            document.getElementById('editItemModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editItemModal').classList.add('hidden');
        }

        // Handle edit form submit
        document.getElementById('editItemForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const itemId = document.getElementById('editItemId').value;
            const data = {
                quantity: parseInt(document.getElementById('editItemQuantity').value),
                user_group_id: document.getElementById('editItemGroup').value || null,
                expiry_date: document.getElementById('editItemExpiry').value || null,
                location: document.getElementById('editItemLocation').value || null,
                notes: document.getElementById('editItemNotes').value || null
            };

            const response = await fetch(`/api/pantry/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                closeEditModal();
                loadItems();
            } else {
                alert('Nie udało się zaktualizować produktu');
            }
        });

        // Consume item
        function consumeItem(id) {
            const item = allItems.find(i => i.id === id);
            if (!item) return;

            if (item.quantity > 1) {
                // Show quantity modal
                document.getElementById('consumeItemId').value = item.id;
                document.getElementById('consumeItemName').textContent = item.product.name;
                document.getElementById('consumeItemAvailable').textContent = item.quantity;
                document.getElementById('consumeQuantity').value = 1;
                document.getElementById('consumeQuantity').max = item.quantity;
                document.getElementById('consumeModal').classList.remove('hidden');
            } else {
                // Consume single item directly
                confirmConsume(id, 1);
            }
        }

        function closeConsumeModal() {
            document.getElementById('consumeModal').classList.add('hidden');
        }

        // Handle consume form submit
        document.getElementById('consumeForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const itemId = document.getElementById('consumeItemId').value;
            const quantity = parseInt(document.getElementById('consumeQuantity').value);
            const maxQuantity = parseInt(document.getElementById('consumeQuantity').max);

            if (quantity > maxQuantity) {
                alert(`Nie możesz zużyć więcej niż ${maxQuantity} sztuk`);
                return;
            }

            closeConsumeModal();
            confirmConsume(itemId, quantity);
        });

        async function confirmConsume(id, quantity) {
            const response = await fetch(`/api/pantry/${id}/consume`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    quantity: quantity
                })
            });

            if (response.ok) {
                loadItems();
            } else {
                alert('Nie udało się oznaczyć produktu jako zużyty');
            }
        }

        // Delete item
        async function deleteItem(id) {
            if (!confirm('Czy na pewno chcesz usunąć ten produkt?')) return;

            const response = await fetch(`/api/pantry/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                loadItems();
            }
        }

        // Event listeners
        document.getElementById('filterGroup').addEventListener('change', loadItems);
        document.getElementById('filterExpiry').addEventListener('change', loadItems);
        document.getElementById('filterLocation').addEventListener('input', debounce(loadItems, 500));
        document.getElementById('filterSearch').addEventListener('input', debounce(loadItems, 500));

        // Debounce helper
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Initialize
        loadGroups();
        loadItems();

        // Search EAN Modal functionality
        const searchModal = document.getElementById('searchEanModal');
        const openSearchBtn = document.getElementById('openSearchModal');
        const closeSearchBtn = document.getElementById('closeSearchModal');
        const cancelSearchBtn = document.getElementById('cancelSearch');
        const searchEanForm = document.getElementById('searchEanForm');
        const eanImageInput = document.getElementById('eanImageInput');
        const imagePreview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');

        openSearchBtn.addEventListener('click', () => {
            searchModal.classList.remove('hidden');
        });

        closeSearchBtn.addEventListener('click', () => {
            searchModal.classList.add('hidden');
            searchEanForm.reset();
            imagePreview.classList.add('hidden');
        });

        cancelSearchBtn.addEventListener('click', () => {
            searchModal.classList.add('hidden');
            searchEanForm.reset();
            imagePreview.classList.add('hidden');
        });

        // Image preview and automatic EAN scanning
        eanImageInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = async (e) => {
                    previewImage.src = e.target.result;
                    imagePreview.classList.remove('hidden');

                    // Show scanning status
                    const scanStatus = document.createElement('div');
                    scanStatus.id = 'scanStatus';
                    scanStatus.className = 'mt-2 text-center text-sm text-blue-600';
                    scanStatus.textContent = 'Analizuję kod kreskowy...';
                    imagePreview.appendChild(scanStatus);

                    // Scan the image with Quagga2
                    try {
                        const eanCode = await scanImageForEAN(e.target.result);
                        if (eanCode) {
                            document.getElementById('eanCodeInput').value = eanCode;
                            scanStatus.textContent = `✓ Znaleziono kod: ${eanCode}`;
                            scanStatus.className =
                            'mt-2 text-center text-sm text-green-600 font-medium';
                        } else {
                            scanStatus.textContent = '⚠ Nie znaleziono kodu - wpisz ręcznie';
                            scanStatus.className = 'mt-2 text-center text-sm text-orange-600';
                        }
                    } catch (error) {
                        console.error('Error scanning image:', error);
                        scanStatus.textContent = '⚠ Nie udało się zeskanować - wpisz kod ręcznie';
                        scanStatus.className = 'mt-2 text-center text-sm text-red-600';
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        // Preprocess image for better barcode detection
        function preprocessImage(src) {
            return new Promise((resolve, reject) => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const img = new Image();

                img.onload = () => {
                    const maxWidth = 1920;
                    const maxHeight = 1920;
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth || height > maxHeight) {
                        const ratio = Math.min(maxWidth / width, maxHeight / height);
                        width *= ratio;
                        height *= ratio;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    // Apply filters for better barcode detection
                    ctx.filter = 'contrast(1.3) brightness(1.1) grayscale(1)';
                    ctx.drawImage(canvas, 0, 0);

                    const processedSrc = canvas.toDataURL('image/jpeg', 0.9);
                    resolve(processedSrc);
                };

                img.onerror = () => {
                    reject(new Error('Failed to load image'));
                };

                img.src = src;
            });
        }

        // Try to decode with multiple configurations
        async function scanImageForEAN(imageSrc) {
            // Check if Quagga is available (loaded from scanner.js)
            if (typeof Quagga === 'undefined') {
                console.error('Quagga not loaded - scanner.js may not be loaded');
                return null;
            }

            // Preprocess image first
            let processedSrc;
            try {
                processedSrc = await preprocessImage(imageSrc);
            } catch (error) {
                console.error('Failed to preprocess image:', error);
                return null;
            }

            // Try multiple configurations (same as in scanner.js)
            const configs = [{
                    size: 1280,
                    patchSize: 'large',
                    halfSample: false
                },
                {
                    size: 1600,
                    patchSize: 'large',
                    halfSample: false
                },
                {
                    size: 800,
                    patchSize: 'medium',
                    halfSample: false
                },
                {
                    size: 1280,
                    patchSize: 'medium',
                    halfSample: true
                }
            ];

            for (let i = 0; i < configs.length; i++) {
                const config = configs[i];
                console.log(`Próba ${i + 1}/${configs.length}:`, config);

                const result = await new Promise((resolve) => {
                    Quagga.decodeSingle({
                        src: processedSrc,
                        numOfWorkers: 0,
                        locate: true,
                        decoder: {
                            readers: ['ean_reader', 'ean_8_reader', 'upc_reader', 'upc_e_reader']
                        },
                        locator: {
                            patchSize: config.patchSize,
                            halfSample: config.halfSample
                        }
                    }, (result) => {
                        if (result && result.codeResult && result.codeResult.code) {
                            resolve(result.codeResult.code);
                        } else {
                            resolve(null);
                        }
                    });
                });

                console.log(`Próba ${i + 1} wynik:`, result);

                if (result) {
                    console.log('✓ Kod kreskowy wykryty:', result);
                    return result;
                }
            }

            // All attempts failed
            console.log('Nie znaleziono kodu kreskowego po wszystkich próbach');
            return null;
        }

        // Search by EAN
        searchEanForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const ean = document.getElementById('eanCodeInput').value.trim();
            if (!ean) {
                alert('Wpisz kod EAN');
                return;
            }

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
                displayProductHistory(data.product, data.history, data.total_scans);
            } catch (error) {
                console.error('Error searching product:', error);
                alert('Błąd podczas wyszukiwania produktu');
            }
        });

        function displayProductHistory(product, history, totalScans) {
            const historyDiv = document.getElementById('productHistoryContent');

            let historyHTML = `
                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-start space-x-4">
                        ${product.image_url 
                            ? `<img src="${product.image_url}" class="w-16 h-16 rounded-lg object-cover" alt="${product.name}">`
                            : `<div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center text-2xl">${product.category.icon}</div>`
                        }
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900">${product.name}</h3>
                            <p class="text-sm text-gray-600">${product.category.icon} ${product.category.name}</p>
                            <p class="text-xs font-mono text-gray-500 mt-1">EAN: ${product.ean_code}</p>
                            <p class="text-xs text-blue-600 mt-1">${totalScans} skanowań</p>
                        </div>
                    </div>
                </div>
            `;

            if (history.length > 0) {
                historyHTML += `<div class="space-y-2">`;
                history.forEach(scan => {
                    historyHTML += `
                        <div class="p-3 bg-white border border-gray-200 rounded-lg">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="font-medium text-gray-900">${scan.scanner.name}</span>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>${scan.scanned_at_human}</span>
                                    </div>
                                    ${scan.location ? `<div class="text-xs text-gray-400 mt-1">📍 ${scan.location}</div>` : ''}
                                </div>
                                <div class="text-xs text-gray-400">
                                    ${scan.scanned_at}
                                </div>
                            </div>
                        </div>
                    `;
                });
                historyHTML += `</div>`;
            } else {
                historyHTML += `
                    <div class="text-center py-8 text-gray-500">
                        <p>Brak historii skanowań dla tego produktu</p>
                    </div>
                `;
            }

            historyDiv.innerHTML = historyHTML;
            document.getElementById('productHistory').classList.remove('hidden');
        }
    </script>
</body>

</html>
