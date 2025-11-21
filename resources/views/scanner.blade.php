<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Skaner Kodów - Domowy Inwentarz</title>
    @vite(['resources/css/app.css', 'resources/js/scanner.js'])
</head>

<body class="bg-gray-900 text-white min-h-screen">
    <div class="flex flex-col min-h-screen pb-20">
        <!-- Header -->
        <div class="bg-gray-800 px-4 py-3 flex items-center justify-between">
            <h1 class="text-lg font-bold">Skaner Kodów Kreskowych</h1>
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>

        <!-- Main content -->
        <div class="flex-1 p-4">
            <!-- Scanned Products List -->
            <div id="scannedProductsList" class="hidden mb-4">
                <div class="bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-200">Zeskanowane produkty (<span id="scannedCount">0</span>)
                        </h3>
                        <button id="clearAllBtn" class="text-sm text-red-400 hover:text-red-300">Wyczyść
                            wszystkie</button>
                    </div>
                    <div id="scannedItems" class="space-y-2 max-h-60 overflow-y-auto"></div>
                </div>
            </div>

            <!-- Camera button -->
            <div class="w-full max-w-md mx-auto">
                <input type="file" id="imageInput" accept="image/*" capture="environment" class="hidden">

                <button id="captureBtn"
                    class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-6 px-4 rounded-lg shadow-lg flex flex-col items-center justify-center space-y-3 transition active:scale-95">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-xl">Zrób zdjęcie kodu kreskowego</span>
                    <span class="text-xs text-amber-100">Skanuj po jednym produkcie</span>
                </button>

                <!-- Manual input -->
                <div class="mt-4 text-center space-y-2">
                    <button id="manualInputBtn" class="text-gray-300 text-sm underline hover:text-white">
                        Lub wpisz kod ręcznie
                    </button>
                    <div class="text-gray-500">lub</div>
                    <button id="noEanBtn" class="text-blue-400 text-sm underline hover:text-blue-300">
                        Dodaj produkt bez kodu EAN
                    </button>
                </div>

                <!-- Manual input form -->
                <div id="manualInputForm" class="hidden mt-4 bg-gray-800 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Wpisz kod EAN</label>
                    <div class="flex space-x-2">
                        <input type="text" id="manualEanInput" placeholder="np. 5900008001634" pattern="[0-9]{8,13}"
                            class="flex-1 px-4 py-2 bg-gray-700 text-white border border-gray-600 rounded-md focus:border-amber-500 focus:ring-amber-500">
                        <button id="submitManualEan"
                            class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700">
                            Szukaj
                        </button>
                    </div>
                </div>

                <!-- No EAN products list -->
                <div id="noEanForm" class="hidden mt-4 bg-gray-800 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-300 mb-3">Wybierz produkt bez kodu EAN</label>
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <button type="button" onclick="selectNoEanProduct('Jabłka', '🍎')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🍎</span> Jabłka
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Jajka', '🥚')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🥚</span> Jajka
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Pomidory', '🍅')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🍅</span> Pomidory
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Banany', '🍌')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🍌</span> Banany
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Ogórki', '🥒')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🥒</span> Ogórki
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Papryka', '🫑')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🫑</span> Papryka
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Ziemniaki', '🥔')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🥔</span> Ziemniaki
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Marchewka', '🥕')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🥕</span> Marchewka
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Cebula', '🧅')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🧅</span> Cebula
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Czosnek', '🧄')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🧄</span> Czosnek
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Sałata', '🥬')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🥬</span> Sałata
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Gruszki', '🍐')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🍐</span> Gruszki
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Pomarańcze', '🍊')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🍊</span> Pomarańcze
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Cytryny', '🍋')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🍋</span> Cytryny
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Truskawki', '🍓')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🍓</span> Truskawki
                        </button>
                        <button type="button" onclick="selectNoEanProduct('Winogrona', '🍇')"
                            class="px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-left">
                            <span class="text-xl mr-2">🍇</span> Winogrona
                        </button>
                    </div>
                    <button type="button" onclick="toggleNoEanForm()"
                        class="w-full px-4 py-2 bg-gray-700 text-gray-300 rounded-md hover:bg-gray-600 text-sm">
                        Anuluj
                    </button>
                </div>

                <!-- Preview area -->
                <div id="previewArea" class="hidden mt-6">
                    <div class="bg-gray-800 rounded-lg p-4">
                        <img id="preview" class="w-full rounded-lg mb-4" alt="Preview">
                        <div id="scanStatus" class="text-center">
                            <p class="text-amber-500">Analizuję kod kreskowy...</p>
                            <div
                                class="mt-3 inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-amber-600">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mt-8 max-w-xl mx-auto">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">Wskazówki</h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-amber-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>Zbliż się</strong> - fotografuj tylko sam kod kreskowy</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-amber-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>Prostuj opakowanie</strong> jeśli to butelka/puszka</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-amber-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Dobre oświetlenie, unikaj refleksów i cieni</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-amber-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Obsługiwane: EAN-13, EAN-8, UPC-A, UPC-E</span>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Bottom Action Bar -->
        <div id="bottomActionBar"
            class="hidden fixed bottom-0 left-0 right-0 bg-gray-800 border-t border-gray-700 p-4 shadow-lg"
            style="padding-bottom: calc(env(safe-area-inset-bottom) + 1rem);">
            <div class="max-w-md mx-auto">
                <button id="confirmAddBtn"
                    class="w-full bg-amber-600 text-white px-6 py-4 rounded-lg hover:bg-amber-700 font-bold text-lg shadow-xl">
                    Dodaj <span id="confirmCount">0</span> produktów do magazynu
                </button>
            </div>
        </div>

        <!-- New Product Modal -->
        <div id="newProductModal"
            class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg max-w-md w-full text-gray-900 max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-xl font-bold">Nowy produkt</h3>
                        <button id="closeNewProductModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-md">
                        <p class="text-sm text-amber-800">
                            <strong>Kod: <span id="scannedEan"></span></strong><br>
                            Ten produkt nie istnieje w bazie. Dodaj go, aby kontynuować.<br>
                            <span class="text-xs">Aby dodać produkt bez kodu EAN, użyj formularza dodawania
                                produktów.</span>
                        </p>
                    </div>
                    <form id="createProductForm">
                        <input type="hidden" id="newProductEan" name="ean_code">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nazwa produktu *</label>
                                <input type="text" name="name" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kategoria *</label>
                                <select id="newProductCategory" name="category_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                    <option value="">Wybierz kategorię</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Zdjęcie produktu</label>
                                <input type="file" name="image" accept="image/*" capture="environment"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                                <p class="mt-1 text-xs text-gray-500">Opcjonalne - możesz zrobić zdjęcie lub wybrać z
                                    galerii</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Opis</label>
                                <textarea name="description" rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex space-x-3">
                            <button type="submit"
                                class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700 font-medium">
                                Utwórz produkt
                            </button>
                            <button type="button" id="cancelNewProduct"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 font-medium">
                                Anuluj
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Confirm Add Modal -->
        <div id="confirmModal"
            class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50 overflow-y-auto">
            <div class="bg-white rounded-lg max-w-2xl w-full text-gray-900 max-h-screen overflow-y-auto my-8">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-xl font-bold">Potwierdź dodanie produktów</h3>
                        <button id="closeConfirmModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Global Settings -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold text-gray-700 mb-3">Ustawienia globalne</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Grupa</label>
                                <select id="globalGroupSelect"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                    <option value="">Mój prywatny magazyn</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Termin ważności</label>
                                <input type="date" id="globalExpiryDate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                        </div>
                        <button id="applyGlobalSettings"
                            class="mt-2 text-xs text-amber-600 hover:text-amber-700 font-medium">
                            Zastosuj do wszystkich
                        </button>
                    </div>

                    <!-- Products List -->
                    <div id="confirmProductsList" class="space-y-3 mb-6">
                        <!-- Products will be inserted here -->
                    </div>

                    <div class="flex space-x-3">
                        <button id="finalConfirmBtn"
                            class="flex-1 bg-amber-600 text-white px-6 py-3 rounded-md hover:bg-amber-700 font-bold">
                            Dodaj wszystkie do magazynu
                        </button>
                        <button id="cancelConfirmBtn"
                            class="px-6 py-3 border border-gray-300 rounded-md hover:bg-gray-50 font-medium">
                            Anuluj
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Script is now loaded via Vite -->
</body>

</html>
