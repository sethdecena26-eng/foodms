<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;

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

        // Inventory (read + stock-in for employees)
        Route::get('/inventory', [IngredientController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/{inventory}', [IngredientController::class, 'show'])->name('inventory.show');
        Route::post('/inventory/{ingredient}/stock-in', [IngredientController::class, 'stockIn'])->name('inventory.stock-in');

        // Reports
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/ingredient-usage', [ReportController::class, 'ingredientUsage'])->name('reports.ingredient-usage');
    });

    // ── Admin only ────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {

        // Menu & Costing
        Route::resource('menu-items', MenuItemController::class);
        Route::post('menu-items/{menuItem}/sync-ingredients', [MenuItemController::class, 'syncIngredients'])
             ->name('menu-items.sync-ingredients');

        // Inventory CRUD (admin can create/edit/delete/adjust)
        Route::resource('inventory', IngredientController::class)->except(['index', 'show']);
        Route::post('/inventory/{ingredient}/adjust', [IngredientController::class, 'adjust'])->name('inventory.adjust');

        // User Management (admin creates all accounts here)
        Route::resource('users', UserController::class);

        // Orders management
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });
});

require __DIR__ . '/auth.php';