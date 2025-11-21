<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Moje Grupy - Inwentarz Kuchenny</title>
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
                        <h1 class="text-lg font-bold text-gray-900">Moje Grupy</h1>
                        <p class="text-sm text-gray-500" id="groupCount">Ładowanie...</p>
                    </div>
                </div>
                <button id="createGroupBtn"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition active:scale-95">
                    + Nowa
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6">
        <!-- Loading State -->
        <div id="loadingState" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="text-gray-600 mt-4">Ładowanie grup...</p>
        </div>

        <!-- Groups List -->
        <div id="groupsList" class="hidden space-y-4"></div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden bg-white rounded-lg shadow p-8 text-center">
            <span class="text-5xl mb-4 block">👥</span>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Brak grup</h3>
            <p class="text-gray-600 mb-6">Stwórz swoją pierwszą grupę, aby zarządzać produktami wspólnie z rodziną</p>
            <button onclick="document.getElementById('createGroupBtn').click()"
                class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                Utwórz pierwszą grupę
            </button>
        </div>
    </main>

    <!-- Create Group Modal -->
    <div id="createGroupModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Nowa grupa</h2>
                <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="createGroupForm">
                <div class="mb-4">
                    <label for="groupName" class="block text-sm font-medium text-gray-700 mb-2">
                        Nazwa grupy
                    </label>
                    <input type="text" id="groupName" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        placeholder="np. Rodzina, Dom, Mieszkanie A">
                </div>

                <div class="mb-6">
                    <label for="groupDescription" class="block text-sm font-medium text-gray-700 mb-2">
                        Opis (opcjonalnie)
                    </label>
                    <textarea id="groupDescription" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-none"
                        placeholder="Dodaj opis grupy..."></textarea>
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" id="isDefault"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Ustaw jako domyślną grupę</span>
                    </label>
                </div>

                <div class="flex space-x-3">
                    <button type="button" id="cancelBtn"
                        class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition">
                        Anuluj
                    </button>
                    <button type="submit" id="submitGroupBtn"
                        class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition disabled:opacity-50">
                        Utwórz
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('auth_token');
        const userName = localStorage.getItem('user_name');

        // Elements
        const loadingState = document.getElementById('loadingState');
        const groupsList = document.getElementById('groupsList');
        const emptyState = document.getElementById('emptyState');
        const createGroupModal = document.getElementById('createGroupModal');
        const createGroupBtn = document.getElementById('createGroupBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const createGroupForm = document.getElementById('createGroupForm');
        const groupCount = document.getElementById('groupCount');

        // Check auth
        if (!token) {
            window.location.href = '{{ route('login') }}';
        }

        // Load groups on page load
        loadGroups();

        // Modal handlers
        createGroupBtn.addEventListener('click', () => {
            createGroupModal.classList.remove('hidden');
        });

        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        createGroupModal.addEventListener('click', (e) => {
            if (e.target === createGroupModal) {
                closeModal();
            }
        });

        function closeModal() {
            createGroupModal.classList.add('hidden');
            createGroupForm.reset();
        }

        // Create group
        createGroupForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = document.getElementById('submitGroupBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Tworzenie...';

            try {
                const response = await fetch('{{ route('api.groups.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token,
                        name: document.getElementById('groupName').value,
                        description: document.getElementById('groupDescription').value || null,
                        is_default: document.getElementById('isDefault').checked,
                    })
                });

                const data = await response.json();

                if (data.success) {
                    closeModal();
                    loadGroups();
                } else {
                    alert(data.message || 'Wystąpił błąd podczas tworzenia grupy');
                }
            } catch (error) {
                console.error('Create group error:', error);
                alert('Wystąpił błąd połączenia');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Utwórz';
            }
        });

        // Load groups
        async function loadGroups() {
            try {
                loadingState.classList.remove('hidden');
                groupsList.classList.add('hidden');
                emptyState.classList.add('hidden');

                const response = await fetch('{{ route('api.groups.index') }}', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    if (response.status === 401) {
                        localStorage.removeItem('auth_token');
                        localStorage.removeItem('user_name');
                        window.location.href = '{{ route('login') }}';
                        return;
                    }
                    throw new Error('Unauthorized - Invalid token');
                }

                const data = await response.json();

                if (data.success) {
                    displayGroups(data.groups);
                    groupCount.textContent = `${data.groups.length} ${data.groups.length === 1 ? 'grupa' : 'grup'}`;
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Load groups error:', error);
                alert('Wystąpił błąd podczas ładowania grup');
            } finally {
                loadingState.classList.add('hidden');
            }
        }

        // Display groups
        function displayGroups(groups) {
            if (groups.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }

            groupsList.innerHTML = groups.map(group => `
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-1">
                                <h3 class="text-lg font-semibold text-gray-900">${escapeHtml(group.name)}</h3>
                                ${group.is_default ? '<span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Domyślna</span>' : ''}
                            </div>
                            ${group.description ? `<p class="text-sm text-gray-600 mb-2">${escapeHtml(group.description)}</p>` : ''}
                            <div class="flex items-center space-x-4 text-xs text-gray-500">
                                <span>👥 ${group.members_count} ${group.members_count === 1 ? 'członek' : 'członków'}</span>
                                <span>📌 ${getRoleLabel(group.role)}</span>
                            </div>
                        </div>
                        <button 
                            onclick="viewGroup(${group.id})"
                            class="text-blue-600 hover:text-blue-700"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="text-xs text-gray-400">
                        Utworzona przez: ${escapeHtml(group.created_by.name)}
                    </div>
                </div>
            `).join('');

            groupsList.classList.remove('hidden');
        }

        function viewGroup(groupId) {
            window.location.href = `/groups/${groupId}`;
        }

        function getRoleLabel(role) {
            const labels = {
                'owner': 'Właściciel',
                'admin': 'Administrator',
                'member': 'Członek'
            };
            return labels[role] || role;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>

</html>
