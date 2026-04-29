<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\FoodSafetyController;
use App\Http\Controllers\WasteController;

use App\Http\Controllers\ArchiveController;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ── Block self-registration entirely ─────────────────────────────────────────
// Breeze registers GET/POST /register in auth.php — we override both here
// so they redirect to login instead. Admin creates accounts via /users.
Route::get('/register',  fn () => redirect()->route('login'));
Route::post('/register', fn () => redirect()->route('login'));

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'has.role'])->group(function () {

    // Dashboard — all roles (including a no-role user gets a safe page)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Employee + Admin ──────────────────────────────────────────────────────
    Route::middleware('role:admin,employee')->group(function () {

        // POS
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/order', [OrderController::class, 'store'])
             ->middleware('throttle:20,1')   // max 20 order submissions per minute
             ->name('orders.store');
        Route::get('/pos/order/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');

        // Inventory — employees can view index, view detail, and stock-in
        Route::get('/inventory',               [IngredientController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/{ingredient}/stock-in', [IngredientController::class, 'stockIn'])->name('inventory.stock-in');

        // Reports
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/ingredient-usage', [ReportController::class, 'ingredientUsage'])->name('reports.ingredient-usage');
        Route::get('/reports/waste', [ReportController::class, 'waste'])->name('reports.waste');
        Route::get('/reports/food-safety', [ReportController::class, 'foodSafety'])->name('reports.food-safety');

        // ── Food Safety & Compliance ───────────────────────────────────────────
        Route::get('/food-safety/temperature',  [FoodSafetyController::class, 'temperatureIndex'])->name('food-safety.temperature');
        Route::post('/food-safety/temperature', [FoodSafetyController::class, 'storeTemperature'])->name('food-safety.temperature.store');
        Route::delete('/food-safety/temperature/{temperatureLog}', [FoodSafetyController::class, 'destroyTemperature'])->name('food-safety.temperature.destroy');

        Route::get('/food-safety/haccp',  [FoodSafetyController::class, 'haccpIndex'])->name('food-safety.haccp');
        Route::post('/food-safety/haccp', [FoodSafetyController::class, 'storeHaccp'])->name('food-safety.haccp.store');

        // ── Waste & Expiry Management ─────────────────────────────────────────
        Route::get('/waste',                [WasteController::class, 'index'])->name('waste.index');
        Route::post('/waste',               [WasteController::class, 'store'])->name('waste.store');
        Route::delete('/waste/{wasteLog}',  [WasteController::class, 'destroy'])->name('waste.destroy');
    });

    // ── Admin only ────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {

        // Menu & Costing
        Route::resource('menu-items', MenuItemController::class);
        Route::post('menu-items/{menuItem}/sync-ingredients', [MenuItemController::class, 'syncIngredients'])
             ->name('menu-items.sync-ingredients');

        // Inventory CRUD — full resource for admin (create, store, show, edit, update, destroy)
        // index is already defined in the employee group above
        Route::resource('inventory', IngredientController::class)->except(['index']);
        Route::post('/inventory/{ingredient}/adjust', [IngredientController::class, 'adjust'])->name('inventory.adjust');

        // User Management (admin creates all accounts here)
        Route::resource('users', UserController::class);

        // Orders management
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

        // HACCP template item management (admin only)
        Route::get('/food-safety/haccp-items',                    [FoodSafetyController::class, 'haccpItems'])->name('food-safety.haccp-items');
        Route::post('/food-safety/haccp-items',                   [FoodSafetyController::class, 'storeHaccpItem'])->name('food-safety.haccp-items.store');
        Route::put('/food-safety/haccp-items/{haccpItem}',        [FoodSafetyController::class, 'updateHaccpItem'])->name('food-safety.haccp-items.update');
        Route::delete('/food-safety/haccp-items/{haccpItem}',     [FoodSafetyController::class, 'destroyHaccpItem'])->name('food-safety.haccp-items.destroy');

        // ── Archive & Restore ─────────────────────────────────────────────────
        // Archive pages (view soft-deleted records)
        Route::get('/archives/menu-items',   [ArchiveController::class, 'menuItems'])->name('archives.menu-items');
        Route::get('/archives/ingredients',  [ArchiveController::class, 'ingredients'])->name('archives.ingredients');
        Route::get('/archives/users',        [ArchiveController::class, 'users'])->name('archives.users');

        // Archive (soft-delete) actions
        Route::delete('/archives/menu-items/{menuItem}/archive',   [ArchiveController::class, 'archiveMenuItem'])->name('archives.menu-items.archive');
        Route::delete('/archives/ingredients/{ingredient}/archive', [ArchiveController::class, 'archiveIngredient'])->name('archives.ingredients.archive');
        Route::delete('/archives/users/{user}/archive',            [ArchiveController::class, 'archiveUser'])->name('archives.users.archive');

        // Restore actions
        Route::patch('/archives/menu-items/{id}/restore',   [ArchiveController::class, 'restoreMenuItem'])->name('archives.menu-items.restore');
        Route::patch('/archives/ingredients/{id}/restore',  [ArchiveController::class, 'restoreIngredient'])->name('archives.ingredients.restore');
        Route::patch('/archives/users/{id}/restore',        [ArchiveController::class, 'restoreUser'])->name('archives.users.restore');

        // Permanent delete (irreversible — requires double confirm in UI)
        Route::delete('/archives/menu-items/{id}/force',   [ArchiveController::class, 'forceDeleteMenuItem'])->name('archives.menu-items.force');
        Route::delete('/archives/ingredients/{id}/force',  [ArchiveController::class, 'forceDeleteIngredient'])->name('archives.ingredients.force');
        Route::delete('/archives/users/{id}/force',        [ArchiveController::class, 'forceDeleteUser'])->name('archives.users.force');
    });
});

require __DIR__ . '/auth.php';