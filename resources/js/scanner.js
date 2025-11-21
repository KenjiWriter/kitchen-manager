import Quagga from '@ericblade/quagga2';

// Export Quagga globally for use in other scripts
window.Quagga = Quagga;

let userGroups = [];
let categories = [];
let scannedProducts = []; // Lista zeskanowanych produktów

// Load groups
async function loadGroups() {
    if (userGroups.length > 0) {
        console.log('Groups already loaded:', userGroups.length);
        return; // Already loaded
    }

    console.log('Loading groups from API...');
    const response = await fetch('/api/groups');
    const data = await response.json();
    userGroups = data.groups;
    console.log('Groups loaded:', userGroups.length, userGroups);
}

// Populate group select (call after groups are loaded)
function populateGroupSelect(selectId) {
    const select = document.getElementById(selectId);
    if (!select) {
        console.warn('Select not found:', selectId);
        return;
    }

    console.log('Populating select:', selectId, 'Groups count:', userGroups.length);

    // Completely rebuild the select
    select.innerHTML = '<option value="">Mój prywatny magazyn</option>';

    // Add groups
    userGroups.forEach(group => {
        const option = document.createElement('option');
        option.value = group.id;
        option.textContent = group.name;
        select.appendChild(option);
        console.log('Added group option:', group.name);
    });

    console.log('Select populated. Final options:', select.options.length);
}

// Load categories
async function loadCategories() {
    const response = await fetch('/api/categories');
    const data = await response.json();
    categories = data.categories;

    const select = document.getElementById('newProductCategory');
    categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = `${cat.icon} ${cat.name}`;
        select.appendChild(option);
    });
}

// Handle image capture
document.getElementById('captureBtn').addEventListener('click', () => {
    document.getElementById('imageInput').click();
});

// Manual input toggle
document.getElementById('manualInputBtn').addEventListener('click', () => {
    const manualForm = document.getElementById('manualInputForm');
    const noEanForm = document.getElementById('noEanForm');

    // Toggle manual form
    manualForm.classList.toggle('hidden');

    // Hide no EAN form if visible
    if (!noEanForm.classList.contains('hidden')) {
        noEanForm.classList.add('hidden');
    }
});

// No EAN button toggle
document.getElementById('noEanBtn').addEventListener('click', () => {
    const manualForm = document.getElementById('manualInputForm');
    const noEanForm = document.getElementById('noEanForm');

    // Toggle no EAN form
    noEanForm.classList.toggle('hidden');

    // Hide manual form if visible
    if (!manualForm.classList.contains('hidden')) {
        manualForm.classList.add('hidden');
    }
});

// Toggle no EAN form (for cancel button)
window.toggleNoEanForm = function () {
    document.getElementById('noEanForm').classList.add('hidden');
};

// Select no EAN product
window.selectNoEanProduct = async function (productName, emoji) {
    console.log('Selecting no-EAN product:', productName);

    try {
        // Get auth token
        const token = localStorage.getItem('auth_token');
        if (!token) {
            alert('Musisz być zalogowany');
            window.location.href = '/login';
            return;
        }

        // Search for product by name
        const response = await fetch(`/api/products?search=${encodeURIComponent(productName)}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to search products');
        }

        const data = await response.json();

        let product = null;

        // Check if product exists (exact match or close match)
        if (data.products && data.products.length > 0) {
            // Try exact match first (with emoji)
            product = data.products.find(p => p.name.toLowerCase() === `${emoji} ${productName}`.toLowerCase());

            // Try without emoji
            if (!product) {
                product = data.products.find(p => p.name.toLowerCase() === productName.toLowerCase());
            }

            // Try partial match (name contains the search term)
            if (!product) {
                product = data.products.find(p => p.name.toLowerCase().includes(productName.toLowerCase()));
            }
        }

        if (product) {
            // Product exists, add to list
            addProductToList(product);
            document.getElementById('noEanForm').classList.add('hidden');

            // Show info message
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            notification.textContent = `Dodano: ${product.name}`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 2000);
        } else {
            // Product doesn't exist, open create modal with pre-filled name
            showCreateProductModal(productName, emoji);
        }
    } catch (error) {
        console.error('Error searching product:', error);
        alert('Błąd podczas wyszukiwania produktu');
    }
};

// Show create product modal with pre-filled name
function showCreateProductModal(productName, emoji) {
    document.getElementById('noEanForm').classList.add('hidden');

    const modal = document.getElementById('newProductModal');
    const form = document.getElementById('createProductForm');
    const nameInput = document.querySelector('#createProductForm input[name="name"]');
    const eanInput = document.getElementById('newProductEan');
    const scannedEanDisplay = document.getElementById('scannedEan');
    const eanInfoBox = scannedEanDisplay?.closest('.mb-4');

    // Reset form first
    form.reset();

    // Pre-fill name with emoji
    if (nameInput) {
        nameInput.value = `${emoji} ${productName}`;
    }

    // Clear EAN since this is a no-EAN product
    if (eanInput) {
        eanInput.value = '';
    }

    // Hide EAN info box for no-EAN products
    if (eanInfoBox) {
        eanInfoBox.style.display = 'none';
    }

    modal.classList.remove('hidden');
    if (nameInput) {
        nameInput.focus();
    }
}

// Manual EAN submit
document.getElementById('submitManualEan').addEventListener('click', () => {
    const eanInput = document.getElementById('manualEanInput');
    const ean = eanInput.value.trim();

    if (ean.length < 8 || ean.length > 13) {
        alert('Kod EAN musi mieć 8-13 cyfr');
        return;
    }

    if (!/^\d+$/.test(ean)) {
        alert('Kod EAN może zawierać tylko cyfry');
        return;
    }

    searchProductAndAdd(ean);
    eanInput.value = '';
});

// Allow Enter key in manual input
document.getElementById('manualEanInput').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        document.getElementById('submitManualEan').click();
    }
});

// Image input change - always single image
document.getElementById('imageInput').addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
        const img = document.getElementById('preview');
        img.src = event.target.result;

        document.getElementById('previewArea').classList.remove('hidden');
        document.getElementById('scanStatus').innerHTML = `
            <p class="text-amber-500">Analizuję kod kreskowy...</p>
            <div class="mt-3 inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-amber-600"></div>
        `;

        preprocessAndDecode(event.target.result);
    };
    reader.readAsDataURL(file);

    // Reset input
    e.target.value = '';
});

// Preprocess image for better barcode detection
function preprocessAndDecode(src) {
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

        canvas.style.filter = 'contrast(1.3) brightness(1.1) grayscale(1)';

        const processedSrc = canvas.toDataURL('image/jpeg', 0.9);
        tryDecode(processedSrc);
    };

    img.onerror = () => {
        document.getElementById('scanStatus').innerHTML = `
            <p class="text-red-500">Nie udało się wczytać zdjęcia</p>
        `;
    };

    img.src = src;
}

// Try to decode with multiple configurations
async function tryDecode(src) {
    const configs = [
        { size: 1280, patchSize: 'large', halfSample: false },
        { size: 1600, patchSize: 'large', halfSample: false },
        { size: 800, patchSize: 'medium', halfSample: false },
        { size: 1280, patchSize: 'medium', halfSample: true }
    ];

    for (let i = 0; i < configs.length; i++) {
        const config = configs[i];
        console.log(`Attempt ${i + 1}/${configs.length}:`, config);

        const result = await new Promise((resolve) => {
            Quagga.decodeSingle({
                src: src,
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

        console.log(`Attempt ${i + 1} result:`, result);

        if (result) {
            console.log('✓ Barcode detected:', result, 'Format:', result.format || 'unknown');
            playBeep();
            searchProductAndAdd(result);
            return;
        }
    }

    // All attempts failed
    document.getElementById('scanStatus').innerHTML = `
        <p class="text-red-500 font-semibold mb-2">Nie znaleziono kodu kreskowego</p>
        <p class="text-sm text-gray-400">Spróbuj ponownie. Upewnij się, że:</p>
        <ul class="text-sm text-gray-400 mt-2 space-y-1 text-left">
            <li>• Zrób zdjęcie tylko samego kodu kreskowego</li>
            <li>• Prostuj butelkę/opakowanie</li>
            <li>• Unikaj cieni i refleksów</li>
            <li>• Użyj dobrze oświetlonego miejsca</li>
        </ul>
        <div class="flex space-x-2 mt-3">
            <button onclick="location.reload()" class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700">
                Spróbuj ponownie
            </button>
            <button onclick="document.getElementById('manualInputForm').classList.remove('hidden')" class="flex-1 px-4 py-2 border border-gray-400 text-gray-300 rounded-md hover:bg-gray-700">
                Wpisz kod
            </button>
        </div>
    `;
}

// Search for product and add to list
async function searchProductAndAdd(code) {
    try {
        const response = await fetch(`/api/products/search-ean?ean=${code}`);
        const data = await response.json();

        document.getElementById('previewArea').classList.add('hidden');

        if (data.success && data.product) {
            addProductToList(data.product);
        } else {
            showNewProductModal(code);
        }
    } catch (error) {
        console.error('Error searching product:', error);
        alert('Błąd podczas wyszukiwania produktu');
    }
}

// Add product to scanned list
function addProductToList(product) {
    // Check if already scanned
    const existing = scannedProducts.find(p => p.product.id === product.id);
    if (existing) {
        alert('Ten produkt został już zeskanowany!');
        return;
    }

    scannedProducts.push({
        product: product,
        quantity: 1,
        expiry_date: null,
        group_id: null
    });

    updateScannedList();
    playBeep();
}

// Update scanned products list UI
function updateScannedList() {
    const listContainer = document.getElementById('scannedProductsList');
    const itemsContainer = document.getElementById('scannedItems');
    const countSpan = document.getElementById('scannedCount');
    const confirmCount = document.getElementById('confirmCount');
    const bottomBar = document.getElementById('bottomActionBar');

    if (scannedProducts.length === 0) {
        listContainer.classList.add('hidden');
        bottomBar.classList.add('hidden');
        return;
    }

    listContainer.classList.remove('hidden');
    bottomBar.classList.remove('hidden');
    countSpan.textContent = scannedProducts.length;
    confirmCount.textContent = scannedProducts.length;

    itemsContainer.innerHTML = scannedProducts.map((item, index) => `
        <div class="flex items-center space-x-3 p-3 bg-gray-700 rounded-lg">
            ${item.product.image_url
            ? `<img src="${item.product.image_url}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0" alt="${item.product.name}">`
            : `<div class="w-12 h-12 rounded-lg bg-gray-600 flex items-center justify-center text-xl flex-shrink-0">
                    ${item.product.category.icon}
                   </div>`
        }
            <div class="flex-1 min-w-0">
                <h4 class="font-medium text-white truncate">${item.product.name}</h4>
                <p class="text-xs text-gray-400">${item.product.category.icon} ${item.product.category.name}</p>
            </div>
            <button onclick="removeScannedProduct(${index})" class="text-red-400 hover:text-red-300 p-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>
    `).join('');
}

// Remove product from list
window.removeScannedProduct = function (index) {
    scannedProducts.splice(index, 1);
    updateScannedList();
};

// Clear all scanned products
document.getElementById('clearAllBtn').addEventListener('click', () => {
    if (confirm('Czy na pewno chcesz usunąć wszystkie zeskanowane produkty?')) {
        scannedProducts = [];
        updateScannedList();
    }
});

// Show confirm modal
document.getElementById('confirmAddBtn').addEventListener('click', () => {
    showConfirmModal();
});

// Show confirm modal with all products
async function showConfirmModal() {
    const modal = document.getElementById('confirmModal');
    const listContainer = document.getElementById('confirmProductsList');

    // Ensure groups are loaded
    await loadGroups();

    // Populate global group select
    populateGroupSelect('globalGroupSelect');

    listContainer.innerHTML = scannedProducts.map((item, index) => `
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-start space-x-3 mb-3">
                ${item.product.image_url
            ? `<img src="${item.product.image_url}" class="w-16 h-16 rounded-lg object-cover flex-shrink-0" alt="${item.product.name}">`
            : `<div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center text-2xl flex-shrink-0">
                        ${item.product.category.icon}
                       </div>`
        }
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900">${item.product.name}</h4>
                    <p class="text-sm text-gray-600">${item.product.category.icon} ${item.product.category.name}</p>
                    <p class="text-xs text-gray-500 font-mono">EAN: ${item.product.ean_code}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Ilość</label>
                    <input type="number" min="1" value="1" 
                        class="quantity-input w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        data-index="${index}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Termin ważności</label>
                    <input type="date" 
                        class="expiry-input w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        data-index="${index}">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Grupa magazynowa</label>
                <select class="group-select w-full px-3 py-2 border border-gray-300 rounded-md text-sm" data-index="${index}">
                    <option value="">Prywatny magazyn</option>
                    ${userGroups.map(g => `<option value="${g.id}">${g.name}</option>`).join('')}
                </select>
            </div>
        </div>
    `).join('');

    modal.classList.remove('hidden');
}

// Update quantity
document.addEventListener('change', (e) => {
    if (e.target.classList.contains('quantity-input')) {
        const index = parseInt(e.target.dataset.index);
        scannedProducts[index].quantity = parseInt(e.target.value) || 1;
    }
});

// Update expiry date
document.addEventListener('change', (e) => {
    if (e.target.classList.contains('expiry-input')) {
        const index = parseInt(e.target.dataset.index);
        scannedProducts[index].expiry_date = e.target.value || null;
    }
});

// Update group selection
document.addEventListener('change', (e) => {
    if (e.target.classList.contains('group-select')) {
        const index = parseInt(e.target.dataset.index);
        scannedProducts[index].group_id = e.target.value || null;
    }
});

// Apply global settings
document.getElementById('applyGlobalSettings').addEventListener('click', () => {
    const groupId = document.getElementById('globalGroupSelect').value;
    const expiryDate = document.getElementById('globalExpiryDate').value;

    // Update expiry dates in UI and data
    document.querySelectorAll('.expiry-input').forEach((input, index) => {
        if (expiryDate) {
            input.value = expiryDate;
            scannedProducts[index].expiry_date = expiryDate;
        }
    });

    // Update groups in UI and data
    document.querySelectorAll('.group-select').forEach((select, index) => {
        if (groupId !== undefined && groupId !== '') {
            select.value = groupId;
            scannedProducts[index].group_id = groupId || null;
        }
    });

    alert('Ustawienia zastosowane do wszystkich produktów');
});

// Final confirm - add all to pantry
document.getElementById('finalConfirmBtn').addEventListener('click', async () => {
    const button = document.getElementById('finalConfirmBtn');
    button.disabled = true;
    button.textContent = 'Dodawanie...';

    let added = 0;
    let failed = 0;

    // Get auth token
    const token = localStorage.getItem('auth_token');

    for (const item of scannedProducts) {
        const data = {
            product_id: item.product.id,
            user_group_id: item.group_id,
            quantity: item.quantity,
            expiry_date: item.expiry_date
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

                // Record scan history
                try {
                    await fetch('/api/scan-history/record', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            product_id: item.product.id,
                            ean_code: item.product.ean_code,
                            location: 'scanner'
                        })
                    });
                } catch (historyError) {
                    console.error('Failed to record scan history:', historyError);
                }
            } else {
                failed++;
            }
        } catch (error) {
            failed++;
        }
    }

    if (failed === 0) {
        alert(`Dodano ${added} produktów do magazynu!`);
        window.location.href = '/pantry';
    } else {
        alert(`Dodano ${added} produktów. Nie udało się dodać ${failed}.`);
        button.disabled = false;
        button.textContent = 'Dodaj wszystkie do magazynu';
    }
});

// Close confirm modal
document.getElementById('closeConfirmModal').addEventListener('click', () => {
    document.getElementById('confirmModal').classList.add('hidden');
});

document.getElementById('cancelConfirmBtn').addEventListener('click', () => {
    document.getElementById('confirmModal').classList.add('hidden');
});

// Play beep sound
function playBeep() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        gainNode.gain.value = 0.3;
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.1);
    } catch (e) {
        console.log('Audio not available');
    }
}

// Show new product modal
function showNewProductModal(ean) {
    const modal = document.getElementById('newProductModal');
    const form = document.getElementById('createProductForm');
    const scannedEanDisplay = document.getElementById('scannedEan');
    const eanInput = document.getElementById('newProductEan');
    const eanInfoBox = scannedEanDisplay?.closest('.mb-4');

    // Reset form first
    form.reset();

    // Set EAN for scanned products
    if (scannedEanDisplay) {
        scannedEanDisplay.textContent = ean;
    }
    if (eanInput) {
        eanInput.value = ean;
    }

    // Show EAN info box for scanned products
    if (eanInfoBox) {
        eanInfoBox.style.display = 'block';
    }

    modal.classList.remove('hidden');
}

// Create new product
document.getElementById('createProductForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const token = localStorage.getItem('auth_token');
    if (!token) {
        alert('Musisz być zalogowany');
        window.location.href = '/login';
        return;
    }

    const formData = new FormData(e.target);

    const response = await fetch('/api/products', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Authorization': `Bearer ${token}`
        },
        body: formData
    });

    if (response.ok) {
        const data = await response.json();
        alert('Produkt utworzony!');
        document.getElementById('newProductModal').classList.add('hidden');
        document.getElementById('createProductForm').reset();
        addProductToList(data.product);
    } else {
        const error = await response.json();
        console.error('Product creation failed:', error);
        console.error('Response status:', response.status);
        console.error('Full error object:', JSON.stringify(error, null, 2));

        let errorMessage = 'Nie udało się utworzyć produktu';

        if (error.message) {
            errorMessage += '\n\n' + error.message;
        }

        if (error.errors) {
            errorMessage += '\n\nSzczegóły:';
            Object.keys(error.errors).forEach(field => {
                errorMessage += '\n- ' + field + ': ' + error.errors[field].join(', ');
            });
        }

        alert('Błąd: ' + errorMessage);
    }
});

// Close new product modal
document.getElementById('closeNewProductModal').addEventListener('click', () => {
    const modal = document.getElementById('newProductModal');
    const form = document.getElementById('createProductForm');
    const eanInfoBox = document.getElementById('scannedEan')?.closest('.mb-4');

    modal.classList.add('hidden');
    form.reset();

    // Reset EAN info box visibility
    if (eanInfoBox) {
        eanInfoBox.style.display = 'block';
    }
});

document.getElementById('cancelNewProduct').addEventListener('click', () => {
    const modal = document.getElementById('newProductModal');
    const form = document.getElementById('createProductForm');
    const eanInfoBox = document.getElementById('scannedEan')?.closest('.mb-4');

    modal.classList.add('hidden');
    form.reset();

    // Reset EAN info box visibility
    if (eanInfoBox) {
        eanInfoBox.style.display = 'block';
    }
});

// Initialize
loadGroups();
loadCategories();
