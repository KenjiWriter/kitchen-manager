<?php

use App\Http\Controllers\Auth\PasswordlessAuthController;
use App\Http\Controllers\PantryItemController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductScanHistoryController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::get('/login', [PasswordlessAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [PasswordlessAuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [PasswordlessAuthController::class, 'logout'])->name('logout');
Route::post('/auth/verify', [PasswordlessAuthController::class, 'verify'])->name('auth.verify');

// Protected routes
Route::middleware(['auth.passwordless'])->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/groups', function () {
        return view('groups');
    })->name('groups');

    Route::get('/groups/{id}', function ($id) {
        return view('group-detail', ['groupId' => $id]);
    })->name('group.detail');

    Route::get('/products', function () {
        return view('products');
    })->name('products');

    Route::get('/products/create', function () {
        return view('product-create');
    })->name('products.create');

    Route::get('/pantry', function () {
        return view('pantry');
    })->name('pantry');

    Route::get('/scanner', function () {
        return view('scanner');
    })->name('scanner');

    Route::get('/scanner/batch-add', function () {
        return view('scanner-batch');
    })->name('scanner.batch');

    Route::get('/inventory', function () {
        return view('inventory');
    })->name('inventory');

    Route::get('/recipes', function () {
        return view('recipes');
    })->name('recipes');

    Route::get('/shopping-lists', function () {
        return view('shopping-lists');
    })->name('shopping-lists');

    // User Groups API
    Route::prefix('api/groups')->group(function () {
        Route::get('/', [UserGroupController::class, 'index'])->name('api.groups.index');
        Route::post('/', [UserGroupController::class, 'store'])->name('api.groups.store');
        Route::get('/{id}', [UserGroupController::class, 'show'])->name('api.groups.show');
        Route::put('/{id}', [UserGroupController::class, 'update'])->name('api.groups.update');
        Route::delete('/{id}', [UserGroupController::class, 'destroy'])->name('api.groups.destroy');
        Route::post('/{id}/members', [UserGroupController::class, 'addMember'])->name('api.groups.addMember');
        Route::delete('/{id}/members/{userId}', [UserGroupController::class, 'removeMember'])->name('api.groups.removeMember');
    });

    // Users API
    Route::get('/api/users/search', [UserController::class, 'search'])->name('api.users.search');

    // Product Categories API
    Route::prefix('api/categories')->group(function () {
        Route::get('/', [ProductCategoryController::class, 'index'])->name('api.categories.index');
        Route::post('/', [ProductCategoryController::class, 'store'])->name('api.categories.store');
        Route::put('/{id}', [ProductCategoryController::class, 'update'])->name('api.categories.update');
        Route::delete('/{id}', [ProductCategoryController::class, 'destroy'])->name('api.categories.destroy');
    });

    // Products API
    Route::prefix('api/products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('api.products.index');
        Route::post('/', [ProductController::class, 'store'])->name('api.products.store');
        Route::get('/search-ean', [ProductController::class, 'searchByEan'])->name('api.products.searchByEan');
        Route::get('/{id}', [ProductController::class, 'show'])->name('api.products.show');
        Route::post('/{id}', [ProductController::class, 'update'])->name('api.products.update'); // POST for multipart/form-data
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('api.products.destroy');
    });

    // Pantry Items API
    Route::prefix('api/pantry')->group(function () {
        Route::get('/', [PantryItemController::class, 'index'])->name('api.pantry.index');
        Route::post('/', [PantryItemController::class, 'store'])->name('api.pantry.store');
        Route::get('/expiring-soon', [PantryItemController::class, 'expiringSoon'])->name('api.pantry.expiringSoon');
        Route::get('/statistics', [PantryItemController::class, 'statistics'])->name('api.pantry.statistics');
        Route::get('/{id}', [PantryItemController::class, 'show'])->name('api.pantry.show');
        Route::put('/{id}', [PantryItemController::class, 'update'])->name('api.pantry.update');
        Route::post('/{id}/consume', [PantryItemController::class, 'consume'])->name('api.pantry.consume');
        Route::delete('/{id}', [PantryItemController::class, 'destroy'])->name('api.pantry.destroy');
    });

    // Product Scan History API
    Route::prefix('api/scan-history')->group(function () {
        Route::get('/by-ean', [ProductScanHistoryController::class, 'getByEan'])->name('api.scan-history.by-ean');
        Route::post('/record', [ProductScanHistoryController::class, 'recordScan'])->name('api.scan-history.record');
    });

    // Recipes API
    Route::prefix('api/recipes')->group(function () {
        Route::get('/', [RecipeController::class, 'index'])->name('api.recipes.index');
        Route::get('/search-mealdb', [RecipeController::class, 'searchMealDB'])->name('api.recipes.searchMealDB');
        Route::get('/mealdb-categories', [RecipeController::class, 'getMealDBCategories'])->name('api.recipes.mealdbCategories');
        Route::post('/import-mealdb', [RecipeController::class, 'importFromMealDB'])->name('api.recipes.importMealDB');
        Route::get('/{id}', [RecipeController::class, 'show'])->name('api.recipes.show');
        Route::put('/{id}', [RecipeController::class, 'update'])->name('api.recipes.update');
        Route::delete('/{id}', [RecipeController::class, 'destroy'])->name('api.recipes.destroy');
        Route::post('/{id}/favorite', [RecipeController::class, 'toggleFavorite'])->name('api.recipes.toggleFavorite');
        Route::put('/{recipeId}/ingredients/{ingredientId}', [RecipeController::class, 'updateIngredientMapping'])->name('api.recipes.updateIngredientMapping');
        Route::delete('/{recipeId}/ingredients/{ingredientId}', [RecipeController::class, 'deleteIngredient'])->name('api.recipes.deleteIngredient');
    });

    // Shopping Lists API
    Route::prefix('api/shopping-lists')->group(function () {
        Route::get('/', [ShoppingListController::class, 'index'])->name('api.shoppingLists.index');
        Route::post('/', [ShoppingListController::class, 'store'])->name('api.shoppingLists.store');
        Route::post('/from-recipe', [ShoppingListController::class, 'createFromRecipe'])->name('api.shoppingLists.fromRecipe');
        Route::post('/{listId}/items', [ShoppingListController::class, 'addItem'])->name('api.shoppingLists.addItem');
        Route::post('/{listId}/items/{itemId}/toggle', [ShoppingListController::class, 'toggleItem'])->name('api.shoppingLists.toggleItem');
        Route::delete('/{listId}/items/{itemId}', [ShoppingListController::class, 'deleteItem'])->name('api.shoppingLists.deleteItem');
        Route::delete('/{id}', [ShoppingListController::class, 'destroy'])->name('api.shoppingLists.destroy');
    });
});
