<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Zaloguj się - Domowy Inwentarz Kuchenny</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">🍳 Kuchnia</h1>
                <p class="text-gray-600">Domowy Inwentarz Kuchenny</p>
            </div>

            <!-- Login Form -->
            <div id="loginForm">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Witaj!</h2>
                <p class="text-gray-600 mb-6">Podaj swoje imię, aby rozpocząć korzystanie z aplikacji.</p>

                <form id="nameForm">
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Twoje imię
                        </label>
                        <input type="text" id="name" name="name" required autocomplete="given-name"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                            placeholder="np. Jan">
                        <p id="errorMessage" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    <button type="submit" id="submitBtn"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                        Rozpocznij
                    </button>
                </form>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="hidden text-center py-8">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="text-gray-600 mt-4">Logowanie...</p>
            </div>
        </div>

        <!-- Info -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Nie wymaga hasła - twoje dane są przechowywane lokalnie</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('nameForm');
            const nameInput = document.getElementById('name');
            const submitBtn = document.getElementById('submitBtn');
            const errorMessage = document.getElementById('errorMessage');
            const loginForm = document.getElementById('loginForm');
            const loadingState = document.getElementById('loadingState');

            // Check if user is already logged in
            checkExistingAuth();

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const name = nameInput.value.trim();

                if (!name) {
                    showError('Proszę podać imię');
                    return;
                }

                await login(name);
            });

            async function login(name) {
                try {
                    submitBtn.disabled = true;
                    hideError();

                    const response = await fetch('{{ route('login.submit') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Save token to localStorage
                        localStorage.setItem('auth_token', data.user.token);
                        localStorage.setItem('user_id', data.user.id);
                        localStorage.setItem('user_name', data.user.name);

                        // Show loading state
                        loginForm.classList.add('hidden');
                        loadingState.classList.remove('hidden');

                        // Redirect to dashboard
                        setTimeout(() => {
                            window.location.href = '{{ route('dashboard') }}';
                        }, 500);
                    } else {
                        showError(data.message || 'Wystąpił błąd podczas logowania');
                        submitBtn.disabled = false;
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    showError('Wystąpił błąd połączenia. Spróbuj ponownie.');
                    submitBtn.disabled = false;
                }
            }

            async function checkExistingAuth() {
                const token = localStorage.getItem('auth_token');

                if (!token) return;

                try {
                    const response = await fetch('{{ route('auth.verify') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            token
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // User is already authenticated, redirect to dashboard
                        window.location.href = '{{ route('dashboard') }}';
                    } else {
                        // Token is invalid, clear localStorage
                        localStorage.removeItem('auth_token');
                        localStorage.removeItem('user_id');
                        localStorage.removeItem('user_name');
                    }
                } catch (error) {
                    console.error('Auth verification error:', error);
                }
            }

            function showError(message) {
                errorMessage.textContent = message;
                errorMessage.classList.remove('hidden');
                nameInput.classList.add('border-red-500');
            }

            function hideError() {
                errorMessage.classList.add('hidden');
                nameInput.classList.remove('border-red-500');
            }

            // Clear error on input
            nameInput.addEventListener('input', function() {
                if (!errorMessage.classList.contains('hidden')) {
                    hideError();
                }
            });
        });
    </script>
</body>

</html>
