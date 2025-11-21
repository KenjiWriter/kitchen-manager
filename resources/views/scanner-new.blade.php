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
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <div class="bg-gray-800 px-4 py-3 flex items-center justify-between">
            <h1 class="text-lg font-bold">Skaner Kodów Kreskowych</h1>
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col items-center justify-center p-4">
            <!-- Camera button -->
            <div class="w-full max-w-md">
                <input type="file" id="imageInput" accept="image/*" capture="environment" multiple class="hidden">
                
                <button id="captureBtn" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-6 px-4 rounded-lg shadow-lg flex flex-col items-center justify-center space-y-3 transition active:scale-95">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-xl">Zrób zdjęcia kodów kreskowych</span>
                    <span class="text-xs text-amber-100">Możesz wybrać wiele zdjęć naraz (do 20)</span>
                </button>

                <!-- Manual input -->
                <div class="mt-4 text-center">
                    <button id="manualInputBtn" class="text-gray-300 text-sm underline hover:text-white">
                        Lub wpisz kod ręcznie
                    </button>
                </div>

                <!-- Manual input form -->
                <div id="manualInputForm" class="hidden mt-4 bg-gray-800 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Wpisz kod EAN</label>
                    <div class="flex space-x-2">
                        <input type="text" id="manualEanInput" placeholder="np. 5900008001634" pattern="[0-9]{8,13}"
                            class="flex-1 px-4 py-2 bg-gray-700 text-white border border-gray-600 rounded-md focus:border-amber-500 focus:ring-amber-500">
                        <button id="submitManualEan" class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700">
                            Szukaj
                        </button>
                    </div>
                </div>

                <!-- Preview area -->
                <div id="previewArea" class="hidden mt-6">
                    <div class="bg-gray-800 rounded-lg p-4">
                        <!-- Progress bar -->
                        <div id="batchProgress" class="hidden mb-4">
                            <div class="flex justify-between text-sm text-gray-300 mb-2">
                                <span>Przetwarzanie zdjęć</span>
                                <span id="progressText">0 / 0</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2.5">
                                <div id="progressBar" class="bg-amber-600 h-2.5 rounded-full transition-all" style="width: 0%"></div>
                            </div>
                        </div>

                        <img id="preview" class="w-full rounded-lg mb-4" alt="Preview">
                        <div id="scanStatus" class="text-center">
                            <p class="text-amber-500">Analizuję kod kreskowy...</p>
                            <div class="mt-3 inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-amber-600"></div>
                        </div>

                        <!-- Results list -->
                        <div id="resultsContainer" class="hidden mt-4 space-y-2 max-h-96 overflow-y-auto">
                            <h4 class="font-semibold text-gray-200 mb-2">Znalezione kody:</h4>
                            <div id="resultsList"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mt-8 max-w-xl">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">Wskazówki</h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-amber-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span><strong>Zbliż się</strong> - fotografuj tylko sam kod kreskowy</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-amber-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span><strong>Prostuj opakowanie</strong> jeśli to butelka/puszka</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-amber-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Dobre oświetlenie, unikaj refleksów i cieni</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-amber-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Obsługiwane: EAN-13, EAN-8, UPC-A, UPC-E</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Product Found Modal -->
    <div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-md w-full text-gray-900 max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-xl font-bold">Znaleziono produkt!</h3>
                    <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div id="productInfo">
                    <!-- Product info will be inserted here -->
                </div>
                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Dodaj do magazynu</h4>
                    <form id="addToPantryForm">
                        <input type="hidden" id="productId" name="product_id">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Grupa (opcjonalnie)</label>
                                <select id="modalGroupSelect" name="user_group_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                    <option value="">Mój prywatny magazyn</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ilość *</label>
                                <input type="number" name="quantity" value="1" min="1" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Termin ważności</label>
                                <input type="date" name="expiry_date" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Lokalizacja</label>
                                <input type="text" name="location" placeholder="np. Lodówka, Spiżarnia..."
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notatki</label>
                                <textarea name="notes" rows="2" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex space-x-3">
                            <button type="submit" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700 font-medium">
                                Dodaj do magazynu
                            </button>
                            <button type="button" id="scanAgain" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 font-medium">
                                Skanuj ponownie
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- New Product Modal -->
    <div id="newProductModal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-md w-full text-gray-900 max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-xl font-bold">Nowy produkt</h3>
                    <button id="closeNewProductModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-md">
                    <p class="text-sm text-amber-800">
                        <strong>Kod: <span id="scannedEan"></span></strong><br>
                        Ten produkt nie istnieje w bazie. Dodaj go, aby kontynuować.
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
                            <label class="block text-sm font-medium text-gray-700">Opis</label>
                            <textarea name="description" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex space-x-3">
                        <button type="submit" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700 font-medium">
                            Utwórz produkt
                        </button>
                        <button type="button" id="cancelNewProduct" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 font-medium">
                            Anuluj
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Found Modal -->
    <div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-md w-full text-gray-900">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-xl font-bold">Znaleziono produkt!</h3>
                    <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div id="productInfo">
                    <!-- Product info will be inserted here -->
                </div>
                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Dodaj do magazynu</h4>
                    <form id="addToPantryForm">
                        <input type="hidden" id="productId" name="product_id">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Grupa (opcjonalnie)</label>
                                <select id="modalGroupSelect" name="user_group_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                    <option value="">Mój prywatny magazyn</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ilość *</label>
                                <input type="number" name="quantity" value="1" min="1" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Termin ważności</label>
                                <input type="date" name="expiry_date" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Lokalizacja</label>
                                <input type="text" name="location" placeholder="np. Lodówka, Spiżarnia..."
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notatki</label>
                                <textarea name="notes" rows="2" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex space-x-3">
                            <button type="submit" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700 font-medium">
                                Dodaj do magazynu
                            </button>
                            <button type="button" id="scanAgain" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 font-medium">
                                Skanuj ponownie
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- New Product Modal -->
    <div id="newProductModal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-md w-full text-gray-900 max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-xl font-bold">Nowy produkt</h3>
                    <button id="closeNewProductModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-md">
                    <p class="text-sm text-amber-800">
                        <strong>Kod: <span id="scannedEan"></span></strong><br>
                        Ten produkt nie istnieje w bazie. Dodaj go, aby kontynuować.
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
                            <label class="block text-sm font-medium text-gray-700">Opis</label>
                            <textarea name="description" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex space-x-3">
                        <button type="submit" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700 font-medium">
                            Utwórz produkt
                        </button>
                        <button type="button" id="cancelNewProduct" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 font-medium">
                            Anuluj
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script is now loaded via Vite -->
</body>
</html>


