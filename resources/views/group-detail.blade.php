<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Szczegóły Grupy - Inwentarz Kuchenny</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen pb-20">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('groups') }}" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900" id="groupName">Ładowanie...</h1>
                        <p class="text-sm text-gray-500" id="groupDescription"></p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button id="editGroupBtn" class="text-gray-600 hover:text-gray-900 p-2 hidden" title="Edytuj grupę">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                    </button>
                    <button id="deleteGroupBtn" class="text-red-600 hover:text-red-700 p-2 hidden" title="Usuń grupę">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6">
        <!-- Loading State -->
        <div id="loadingState" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="text-gray-600 mt-4">Ładowanie szczegółów grupy...</p>
        </div>

        <!-- Group Details -->
        <div id="groupDetails" class="hidden space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Członków</p>
                            <p class="text-2xl font-bold text-gray-900" id="membersCount">0</p>
                        </div>
                        <span class="text-3xl">👥</span>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Twoja rola</p>
                            <p class="text-lg font-semibold text-blue-600" id="userRole">-</p>
                        </div>
                        <span class="text-3xl">📌</span>
                    </div>
                </div>
            </div>

            <!-- Members Section -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Członkowie grupy</h2>
                    <button id="addMemberBtn"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition active:scale-95 hidden">
                        + Dodaj
                    </button>
                </div>
                <div id="membersList" class="divide-y divide-gray-200">
                    <!-- Members will be loaded here -->
                </div>
            </div>

            <!-- Creator Info -->
            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-sm text-gray-600">Grupa utworzona przez</p>
                <p class="font-semibold text-gray-900" id="creatorName">-</p>
            </div>
        </div>

        <!-- Error State -->
        <div id="errorState" class="hidden bg-white rounded-lg shadow p-8 text-center">
            <span class="text-5xl mb-4 block">⚠️</span>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Nie można załadować grupy</h3>
            <p class="text-gray-600 mb-6" id="errorMessage"></p>
            <a href="{{ route('groups') }}"
                class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                Wróć do listy grup
            </a>
        </div>
    </main>

    <!-- Add Member Modal -->
    <div id="addMemberModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Dodaj członka</h2>
                <button id="closeAddMemberBtn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="addMemberForm">
                <div class="mb-4">
                    <label for="userSearch" class="block text-sm font-medium text-gray-700 mb-2">
                        Wyszukaj użytkownika po imieniu
                    </label>
                    <input type="text" id="userSearch" autocomplete="off"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        placeholder="Wpisz imię...">
                    <div id="searchResults" class="mt-2 max-h-60 overflow-y-auto hidden"></div>
                </div>

                <div class="mb-6 hidden" id="selectedUserDiv">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Wybrany użytkownik</label>
                    <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                        <span id="selectedUserName" class="font-medium text-gray-900"></span>
                        <button type="button" id="clearSelectionBtn"
                            class="text-red-600 hover:text-red-700 text-sm">
                            Usuń
                        </button>
                    </div>
                </div>

                <div class="mb-6 hidden" id="roleDiv">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rola</label>
                    <select id="memberRole"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                        <option value="member">Członek</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <div class="flex space-x-3">
                    <button type="button" id="cancelAddMemberBtn"
                        class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition">
                        Anuluj
                    </button>
                    <button type="submit" id="submitAddMemberBtn" disabled
                        class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Dodaj
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Group Modal -->
    <div id="editGroupModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Edytuj grupę</h2>
                <button id="closeEditModalBtn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="editGroupForm">
                <div class="mb-4">
                    <label for="editGroupName" class="block text-sm font-medium text-gray-700 mb-2">
                        Nazwa grupy
                    </label>
                    <input type="text" id="editGroupName" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>

                <div class="mb-6">
                    <label for="editGroupDescription" class="block text-sm font-medium text-gray-700 mb-2">
                        Opis
                    </label>
                    <textarea id="editGroupDescription" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-none"></textarea>
                </div>

                <div class="flex space-x-3">
                    <button type="button" id="cancelEditBtn"
                        class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition">
                        Anuluj
                    </button>
                    <button type="submit" id="submitEditBtn"
                        class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
                        Zapisz
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const groupId = {{ $groupId }};
        const token = localStorage.getItem('auth_token');
        const currentUserId = parseInt(localStorage.getItem('user_id'));

        let groupData = null;
        let selectedUserId = null;
        let searchTimeout = null;

        // Elements
        const loadingState = document.getElementById('loadingState');
        const groupDetails = document.getElementById('groupDetails');
        const errorState = document.getElementById('errorState');
        const addMemberModal = document.getElementById('addMemberModal');
        const editGroupModal = document.getElementById('editGroupModal');

        // Check auth
        if (!token) {
            window.location.href = '{{ route('login') }}';
        }

        // Load group details
        loadGroupDetails();

        // Add Member Modal
        document.getElementById('addMemberBtn').addEventListener('click', () => {
            addMemberModal.classList.remove('hidden');
        });

        document.getElementById('closeAddMemberBtn').addEventListener('click', closeAddMemberModal);
        document.getElementById('cancelAddMemberBtn').addEventListener('click', closeAddMemberModal);

        addMemberModal.addEventListener('click', (e) => {
            if (e.target === addMemberModal) {
                closeAddMemberModal();
            }
        });

        // Edit Group Modal
        document.getElementById('editGroupBtn').addEventListener('click', () => {
            if (groupData) {
                document.getElementById('editGroupName').value = groupData.name;
                document.getElementById('editGroupDescription').value = groupData.description || '';
                editGroupModal.classList.remove('hidden');
            }
        });

        document.getElementById('closeEditModalBtn').addEventListener('click', closeEditModal);
        document.getElementById('cancelEditBtn').addEventListener('click', closeEditModal);

        editGroupModal.addEventListener('click', (e) => {
            if (e.target === editGroupModal) {
                closeEditModal();
            }
        });

        // User Search
        document.getElementById('userSearch').addEventListener('input', (e) => {
            const query = e.target.value.trim();

            clearTimeout(searchTimeout);

            if (query.length < 2) {
                document.getElementById('searchResults').classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => searchUsers(query), 300);
        });

        // Clear Selection
        document.getElementById('clearSelectionBtn').addEventListener('click', () => {
            selectedUserId = null;
            document.getElementById('selectedUserDiv').classList.add('hidden');
            document.getElementById('roleDiv').classList.add('hidden');
            document.getElementById('submitAddMemberBtn').disabled = true;
            document.getElementById('userSearch').value = '';
            document.getElementById('searchResults').classList.add('hidden');
        });

        // Add Member Form
        document.getElementById('addMemberForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await addMember();
        });

        // Edit Group Form
        document.getElementById('editGroupForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await editGroup();
        });

        // Delete Group
        document.getElementById('deleteGroupBtn').addEventListener('click', async () => {
            if (!confirm('Czy na pewno chcesz usunąć tę grupę? Ta operacja jest nieodwracalna.')) {
                return;
            }

            try {
                const response = await fetch(`/api/groups/${groupId}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = '{{ route('groups') }}';
                } else {
                    alert(data.message || 'Wystąpił błąd podczas usuwania grupy');
                }
            } catch (error) {
                console.error('Delete group error:', error);
                alert('Wystąpił błąd połączenia');
            }
        });

        async function loadGroupDetails() {
            try {
                const response = await fetch(`/api/groups/${groupId}`, {
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
                    throw new Error('Unauthorized - Invalid token');
                }

                const data = await response.json();

                if (data.success) {
                    groupData = data.group;
                    displayGroupDetails(data.group);
                } else {
                    showError(data.message || 'Nie można załadować szczegółów grupy');
                }
            } catch (error) {
                console.error('Load group error:', error);
                showError('Wystąpił błąd połączenia');
            } finally {
                loadingState.classList.add('hidden');
            }
        }

        function displayGroupDetails(group) {
            document.getElementById('groupName').textContent = group.name;
            document.getElementById('groupDescription').textContent = group.description || '';
            document.getElementById('membersCount').textContent = group.members.length;
            document.getElementById('userRole').textContent = getRoleLabel(group.user_role);
            document.getElementById('creatorName').textContent = group.created_by.name;

            // Show action buttons if user can manage
            if (group.user_role === 'owner' || group.user_role === 'admin') {
                document.getElementById('addMemberBtn').classList.remove('hidden');
                document.getElementById('editGroupBtn').classList.remove('hidden');
            }

            if (group.user_role === 'owner') {
                document.getElementById('deleteGroupBtn').classList.remove('hidden');
            }

            // Display members
            displayMembers(group.members, group.user_role, group.created_by.id);

            groupDetails.classList.remove('hidden');
        }

        function displayMembers(members, userRole, ownerId) {
            const canManage = userRole === 'owner' || userRole === 'admin';

            const html = members.map(member => `
                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <p class="font-medium text-gray-900">${escapeHtml(member.name)}</p>
                            ${member.id === currentUserId ? '<span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Ty</span>' : ''}
                            ${member.id === ownerId ? '<span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Właściciel</span>' : ''}
                        </div>
                        <p class="text-sm text-gray-500">${getRoleLabel(member.role)}</p>
                        <p class="text-xs text-gray-400">Dołączył: ${new Date(member.joined_at).toLocaleDateString('pl-PL')}</p>
                    </div>
                    ${canManage && member.id !== ownerId && member.id !== currentUserId ? `
                                <button 
                                    onclick="removeMember(${member.id}, '${escapeHtml(member.name)}')"
                                    class="text-red-600 hover:text-red-700 text-sm font-medium"
                                >
                                    Usuń
                                </button>
                            ` : ''}
                </div>
            `).join('');

            document.getElementById('membersList').innerHTML = html;
        }

        async function searchUsers(query) {
            try {
                const response = await fetch(`/api/users/search?query=${encodeURIComponent(query)}`, {
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
                    throw new Error('Failed to search users');
                }

                const data = await response.json();

                if (data.success) {
                    displaySearchResults(data.users);
                }
            } catch (error) {
                console.error('Search users error:', error);
            }
        }

        function displaySearchResults(users) {
            const resultsDiv = document.getElementById('searchResults');

            // Filter out current members
            const memberIds = groupData.members.map(m => m.id);
            const availableUsers = users.filter(u => !memberIds.includes(u.id));

            if (availableUsers.length === 0) {
                resultsDiv.innerHTML = '<p class="text-sm text-gray-500 p-2">Brak wyników</p>';
                resultsDiv.classList.remove('hidden');
                return;
            }

            const html = availableUsers.map(user => `
                <button 
                    type="button"
                    onclick="selectUser(${user.id}, '${escapeHtml(user.name)}')"
                    class="w-full text-left p-3 hover:bg-blue-50 rounded-lg transition border border-gray-200 mb-2"
                >
                    <p class="font-medium text-gray-900">${escapeHtml(user.name)}</p>
                    ${user.last_login ? `<p class="text-xs text-gray-500">Ostatnie logowanie: ${user.last_login}</p>` : ''}
                </button>
            `).join('');

            resultsDiv.innerHTML = html;
            resultsDiv.classList.remove('hidden');
        }

        function selectUser(userId, userName) {
            selectedUserId = userId;
            document.getElementById('selectedUserName').textContent = userName;
            document.getElementById('selectedUserDiv').classList.remove('hidden');
            document.getElementById('roleDiv').classList.remove('hidden');
            document.getElementById('submitAddMemberBtn').disabled = false;
            document.getElementById('searchResults').classList.add('hidden');
            document.getElementById('userSearch').value = '';
        }

        async function addMember() {
            if (!selectedUserId) return;

            const submitBtn = document.getElementById('submitAddMemberBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Dodawanie...';

            try {
                const response = await fetch(`/api/groups/${groupId}/members`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: selectedUserId,
                        role: document.getElementById('memberRole').value
                    })
                });

                const data = await response.json();

                if (data.success) {
                    closeAddMemberModal();
                    loadGroupDetails();
                } else {
                    alert(data.message || 'Wystąpił błąd podczas dodawania członka');
                }
            } catch (error) {
                console.error('Add member error:', error);
                alert('Wystąpił błąd połączenia');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Dodaj';
            }
        }

        async function removeMember(userId, userName) {
            if (!confirm(`Czy na pewno chcesz usunąć ${userName} z grupy?`)) {
                return;
            }

            try {
                const response = await fetch(`/api/groups/${groupId}/members/${userId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    loadGroupDetails();
                } else {
                    alert(data.message || 'Wystąpił błąd podczas usuwania członka');
                }
            } catch (error) {
                console.error('Remove member error:', error);
                alert('Wystąpił błąd połączenia');
            }
        }

        async function editGroup() {
            const submitBtn = document.getElementById('submitEditBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Zapisywanie...';

            try {
                const response = await fetch(`/api/groups/${groupId}`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: document.getElementById('editGroupName').value,
                        description: document.getElementById('editGroupDescription').value || null
                    })
                });

                const data = await response.json();

                if (data.success) {
                    closeEditModal();
                    loadGroupDetails();
                } else {
                    alert(data.message || 'Wystąpił błąd podczas edycji grupy');
                }
            } catch (error) {
                console.error('Edit group error:', error);
                alert('Wystąpił błąd połączenia');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Zapisz';
            }
        }

        function closeAddMemberModal() {
            addMemberModal.classList.add('hidden');
            selectedUserId = null;
            document.getElementById('userSearch').value = '';
            document.getElementById('searchResults').classList.add('hidden');
            document.getElementById('selectedUserDiv').classList.add('hidden');
            document.getElementById('roleDiv').classList.add('hidden');
            document.getElementById('submitAddMemberBtn').disabled = true;
            document.getElementById('memberRole').value = 'member';
        }

        function closeEditModal() {
            editGroupModal.classList.add('hidden');
        }

        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            errorState.classList.remove('hidden');
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
