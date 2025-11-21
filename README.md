# 🍽️ Kitchen Inventory Manager

System zarządzania magazynem kuchennym z funkcją skanowania kodów kreskowych, śledzeniem dat ważności i zarządzaniem grupami użytkowników.

## 📋 Spis treści

- [Opis projektu](#opis-projektu)
- [Główne funkcje](#główne-funkcje)
- [Technologie](#technologie)
- [Wymagania systemowe](#wymagania-systemowe)
- [Instalacja](#instalacja)
- [Konfiguracja](#konfiguracja)
- [Uruchomienie](#uruchomienie)
- [Dostęp z innych urządzeń](#dostęp-z-innych-urządzeń)
- [Struktura projektu](#struktura-projektu)
- [Licencja](#licencja)

## 🎯 Opis projektu

Kitchen Inventory Manager to nowoczesna aplikacja webowa do zarządzania produktami spożywczymi w domu. Aplikacja umożliwia:
- Skanowanie kodów kreskowych produktów przy pomocy kamery telefonu
- Śledzenie stanów magazynowych i terminów ważności
- Zarządzanie grupami użytkowników (np. rodzina, współlokatorzy)
- Tworzenie własnej bazy produktów z kategoriami
- Szybkie dodawanie produktów bez kodów EAN (warzywa, owoce, jajka)

## ✨ Główne funkcje

### 🔐 Autentykacja bez hasła
- System logowania z wykorzystaniem tokenów
- Bezpieczne przechowywanie sesji w localStorage
- Nie wymaga zapamiętywania haseł

### 📦 Zarządzanie produktami
- Tworzenie, edycja i usuwanie produktów
- Wsparcie dla kodów EAN-13, EAN-8, UPC-A, UPC-E
- Możliwość dodawania produktów bez kodów kreskowych
- Kategorie produktów z ikonami emoji (🥛 Nabiał, 🥖 Pieczywo, 🥩 Mięso, itp.)
- Zdjęcia produktów (do 10MB)
- Wyszukiwanie po nazwie i kodzie EAN

### 📱 Skaner kodów kreskowych
- Wykorzystanie kamery telefonu do skanowania
- Biblioteka Quagga2 dla rozpoznawania kodów
- Pojedyncze skanowanie z natychmiastowym podglądem
- Manualne wprowadzanie kodów EAN
- Lista szybkiego wyboru produktów bez EAN:
  - 🍎 Jabłka, 🥚 Jajka, 🍅 Pomidory, 🍌 Banany
  - 🥒 Ogórki, 🫑 Papryka, 🥔 Ziemniaki, 🥕 Marchewka
  - 🧅 Cebula, 🧄 Czosnek, 🥬 Sałata, 🍐 Gruszki
  - 🍊 Pomarańcze, 🍋 Cytryny, 🍓 Truskawki, 🍇 Winogrona

### 🏠 Magazyn kuchenny (Pantry)
- Przegląd wszystkich produktów w magazynie
- Filtrowanie po dacie ważności (wszystkie, wygasające, wygasłe)
- Wyszukiwanie po nazwie produktu
- Inteligentne zużywanie produktów z wyborem ilości
- Edycja stanów magazynowych z możliwością przeniesienia między grupami
- Usuwanie produktów

### 👥 Grupy użytkowników
- Tworzenie grup (np. "Rodzina", "Dom", "Współlokatorzy")
- Przypisywanie produktów do grup lub prywatnego magazynu
- Zarządzanie członkami grup
- Osobne stany magazynowe dla każdej grupy

### 📊 Dashboard
- Statystyki magazynu (łączna ilość, wygasające, wygasłe produkty)
- Lista ostatnio dodanych produktów
- Przycisk dostępu do grup
- Dolna nawigacja mobilna (Magazyn, Skanuj, Produkty)

## 🛠 Technologie

### Backend
- **Laravel 12** - Framework PHP
- **PHP 8.2+** - Język programowania
- **SQLite/MySQL** - Baza danych
- **RESTful API** - Architektura komunikacji

### Frontend
- **Blade** - System szablonów Laravel
- **Tailwind CSS 4** - Framework CSS
- **Vite 7** - Build tool
- **Vanilla JavaScript** - Logika frontendu
- **Quagga2** - Biblioteka do skanowania kodów kreskowych

### Dodatkowe
- **localStorage** - Przechowywanie tokenów autoryzacji
- **FormData API** - Upload plików
- **Fetch API** - Komunikacja z backend

## 💻 Wymagania systemowe

- **PHP** 8.2 lub nowszy
- **Composer** (menadżer pakietów PHP)
- **Node.js** 18+ i **npm** (do budowania assetów)
- **SQLite** (domyślnie) lub **MySQL/PostgreSQL**
- Przeglądarka z obsługą kamery (do skanowania kodów)

## 📥 Instalacja

### 1. Sklonuj repozytorium
```bash
git clone <url-repozytorium>
cd kitchen
```

### 2. Zainstaluj zależności PHP
```bash
composer install
```

### 3. Zainstaluj zależności JavaScript
```bash
npm install
```

### 4. Skopiuj plik konfiguracyjny
```bash
# Windows (PowerShell)
copy .env.example .env

# Linux/macOS
cp .env.example .env
```

### 5. Wygeneruj klucz aplikacji
```bash
php artisan key:generate
```

### 6. Utwórz bazę danych
Domyślnie projekt używa SQLite. Utwórz pusty plik bazy:

```bash
# Windows (PowerShell)
New-Item -Path database/database.sqlite -ItemType File

# Linux/macOS
touch database/database.sqlite
```

Lub skonfiguruj MySQL/PostgreSQL w pliku `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kitchen
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Uruchom migracje i seedery
```bash
php artisan migrate --seed
```

To utworzy:
- Tabele w bazie danych
- Kategorie produktów (Owoce, Warzywa, Nabiał, Mięso, itd.)
- Przykładowe produkty bez EAN (Jabłka, Jajka, Pomidory, itd.)

### 8. Utwórz link do storage
```bash
php artisan storage:link
```

### 9. Zbuduj assety frontendowe
```bash
npm run build
```

## ⚙️ Konfiguracja

### Plik `.env`

Kluczowe ustawienia:

```env
APP_NAME="Kitchen Inventory"
APP_ENV=local
APP_KEY=base64:... # Wygenerowane przez artisan key:generate
APP_DEBUG=true
APP_TIMEZONE=Europe/Warsaw
APP_URL=http://192.168.1.171:8080

# Baza danych
DB_CONNECTION=sqlite
# DB_DATABASE=absolute/path/to/database.sqlite

# Sesje (używamy API tokens zamiast sesji)
SESSION_DRIVER=file
```

## 🚀 Uruchomienie

### Uruchom serwer deweloperski

```bash
php artisan serve --host=0.0.0.0 --port=8080
```

Aplikacja będzie dostępna pod adresem:
- **Lokalnie**: `http://localhost:8080`
- **W sieci lokalnej**: `http://<twoje-ip>:8080`

### Zbuduj assety (tryb deweloperski)

Jeśli planujesz zmieniać kod JavaScript/CSS:

```bash
npm run dev
```

Dla produkcji:
```bash
npm run build
```

## 🌐 Dostęp z innych urządzeń

### 1. Sprawdź swój adres IP

#### Windows (PowerShell)
```powershell
ipconfig
```
Szukaj linii **"IPv4 Address"** dla aktywnego połączenia sieciowego (np. `192.168.1.171`)

#### Linux/macOS
```bash
ip addr show
# lub
ifconfig
```

### 2. Zaktualizuj APP_URL w .env

```env
APP_URL=http://192.168.1.171:8080
```

### 3. Uruchom serwer z bindowaniem do wszystkich interfejsów

```bash
php artisan serve --host=0.0.0.0 --port=8080
```

### 4. Dostęp z telefonu/tabletu

Na urządzeniu mobilnym w tej samej sieci WiFi:
1. Otwórz przeglądarkę (Safari, Chrome)
2. Wpisz adres: `http://192.168.1.171:8080`
3. Zaloguj się (system utworzy token)
4. Skanuj kody kreskowe używając kamery urządzenia!

### 🔥 Firewall

Jeśli nie możesz połączyć się z innego urządzenia, upewnij się że:

#### Windows
```powershell
# Dodaj regułę firewall dla portu 8080
New-NetFirewallRule -DisplayName "Laravel Dev Server" -Direction Inbound -LocalPort 8080 -Protocol TCP -Action Allow
```

#### Linux (ufw)
```bash
sudo ufw allow 8080/tcp
```

## 📁 Struktura projektu

```
kitchen/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          # Autentykacja
│   │   │   ├── ProductController.php       # CRUD produktów
│   │   │   ├── ProductCategoryController.php
│   │   │   ├── PantryItemController.php    # Magazyn kuchenny
│   │   │   └── UserGroupController.php     # Grupy użytkowników
│   │   └── Middleware/
│   │       └── TokenAuth.php               # Middleware tokenu
│   └── Models/
│       ├── User.php
│       ├── Product.php
│       ├── ProductCategory.php
│       ├── PantryItem.php
│       └── UserGroup.php
├── database/
│   ├── migrations/                          # Migracje bazy danych
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── ProductCategorySeeder.php
├── public/
│   ├── build/                               # Skompilowane assety (Vite)
│   └── storage/                             # Link symboliczny do storage
├── resources/
│   ├── css/
│   │   └── app.css                          # Tailwind CSS
│   ├── js/
│   │   ├── app.js                           # Główny plik JS
│   │   └── scanner.js                       # Logika skanera (Quagga2)
│   └── views/
│       ├── dashboard.blade.php              # Dashboard
│       ├── scanner.blade.php                # Skaner kodów
│       ├── pantry.blade.php                 # Magazyn
│       ├── products.blade.php               # Lista produktów
│       ├── product-create.blade.php         # Dodawanie produktu
│       ├── groups.blade.php                 # Lista grup
│       └── group-detail.blade.php           # Szczegóły grupy
├── routes/
│   ├── web.php                              # Routing widoków
│   └── api.php                              # API endpoints
├── storage/
│   ├── app/
│   │   └── public/
│   │       └── products/                    # Zdjęcia produktów
│   └── logs/
│       └── laravel.log                      # Logi aplikacji
├── .env                                     # Konfiguracja środowiska
├── composer.json                            # Zależności PHP
├── package.json                             # Zależności JavaScript
└── vite.config.js                           # Konfiguracja Vite
```

## 🎨 Główne endpointy API

### Produkty
- `GET /api/products` - Lista produktów (z filtrowaniem)
- `POST /api/products` - Utworzenie produktu
- `GET /api/products/{id}` - Szczegóły produktu
- `POST /api/products/{id}` - Aktualizacja produktu (FormData)
- `DELETE /api/products/{id}` - Usunięcie produktu
- `GET /api/products/search-ean?ean={code}` - Wyszukiwanie po EAN

### Magazyn (Pantry)
- `GET /api/pantry` - Lista produktów w magazynie
- `POST /api/pantry` - Dodanie produktów do magazynu
- `PUT /api/pantry/{id}` - Aktualizacja stanu magazynowego
- `POST /api/pantry/{id}/consume` - Zużycie produktu (z ilością)
- `DELETE /api/pantry/{id}` - Usunięcie z magazynu

### Grupy
- `GET /api/groups` - Lista grup użytkownika
- `POST /api/groups` - Utworzenie grupy
- `GET /api/groups/{id}` - Szczegóły grupy
- `PUT /api/groups/{id}` - Aktualizacja grupy
- `DELETE /api/groups/{id}` - Usunięcie grupy

### Kategorie
- `GET /api/categories` - Lista kategorii produktów

Wszystkie endpointy wymagają nagłówka `Authorization: Bearer {token}`.

## 🔒 Bezpieczeństwo

- **CSRF Protection** - Wszystkie formularze chronione tokenem CSRF
- **Token Authentication** - API wymaga tokenu w nagłówku Authorization
- **File Upload Validation** - Zdjęcia produktów: JPEG/PNG/WebP, max 10MB
- **Input Validation** - Walidacja wszystkich danych wejściowych
- **SQL Injection Prevention** - Eloquent ORM z prepared statements

## 🐛 Rozwiązywanie problemów

### Problem: "Błąd: Nie udało się utworzyć produktu"
**Rozwiązanie:** Sprawdź logi w `storage/logs/laravel.log` lub konsolę przeglądarki (F12 → Console)

### Problem: Nie można połączyć się z telefonu
**Rozwiązanie:** 
1. Sprawdź czy telefon i komputer są w tej samej sieci WiFi
2. Wyłącz firewall lub dodaj wyjątek dla portu 8080
3. Upewnij się że serwer działa z `--host=0.0.0.0`

### Problem: Zdjęcia produktów nie wyświetlają się
**Rozwiązanie:**
```bash
php artisan storage:link
```

### Problem: Assety CSS/JS nie ładują się
**Rozwiązanie:**
```bash
npm run build
php artisan optimize:clear
```

## 📝 Licencja

**CC BY-NC 4.0 (Creative Commons Attribution-NonCommercial 4.0 International)**

Copyright © 2025 Kitchen Inventory Manager

Niniejszy projekt jest udostępniony na licencji Creative Commons Attribution-NonCommercial 4.0 International License.

### Możesz:
✅ **Używać** - kopiować i wykorzystywać materiał w dowolnym medium i formacie  
✅ **Modyfikować** - remiksować, przekształcać i tworzyć na podstawie materiału  
✅ **Dzielić się** - kopiować i rozpowszechniać materiał  

### Pod następującymi warunkami:
📌 **Uznanie autorstwa** - musisz podać autora, link do licencji i zaznaczyć czy wprowadzono zmiany  
🚫 **Użytek niekomercyjny** - nie możesz używać materiału w celach komercyjnych  

### Oznacza to że:
- ✅ Możesz używać aplikacji w domu/dla rodziny
- ✅ Możesz modyfikować kod dla własnych potrzeb
- ✅ Możesz hostować dla siebie i znajomych (niekomercyjnie)
- ❌ **Nie możesz** sprzedawać aplikacji
- ❌ **Nie możesz** oferować jako płatnej usługi (SaaS)
- ❌ **Nie możesz** używać w firmie komercyjnej

Pełny tekst licencji: https://creativecommons.org/licenses/by-nc/4.0/legalcode.pl

---

## 👨‍💻 Autor

Stworzono z ❤️ dla łatwiejszego zarządzania kuchnią.

## 🙏 Podziękowania

- **Laravel** - Framework PHP
- **Tailwind CSS** - Framework CSS
- **Quagga2** - Biblioteka skanowania kodów kreskowych
- **Vite** - Szybki build tool

---

**Pytania?** Sprawdź logi w `storage/logs/laravel.log` lub konsolę przeglądarki (F12).

**Powodzenia!** 🚀

