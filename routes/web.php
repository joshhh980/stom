<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    // Main Dashboard Landing Route
    Route::get('/', [Controllers\DashboardController::class, 'index'])->name('dashboard');


    Route::resource('purchase-orders', Controllers\PurchaseOrderController::class);
    Route::post('purchase-orders/import', [Controllers\PurchaseOrderController::class, "import"])->name('purchase-orders.import');

    Route::resource('back-orders', Controllers\BackOrderController::class);
    
    Route::get('/receiving', [Controllers\DashboardController::class, 'receiving'])->name('receiving.index');
    Route::get('/stocks', [Controllers\DashboardController::class, 'stocks'])->name('stocks.index');
    Route::get('/sales', [Controllers\DashboardController::class, 'sales'])->name('sales.index');

    // Maintenance stubs handled by Controllers\DashboardController
    Route::get('/suppliers', [Controllers\DashboardController::class, 'suppliers'])->name('suppliers.index');
    Route::get('/items', [Controllers\DashboardController::class, 'items'])->name('items.index');
    Route::get('/users', [Controllers\DashboardController::class, 'users'])->name('users.index');
    Route::get('/settings', [Controllers\DashboardController::class, 'settings'])->name('settings.index');
});

require __DIR__ . '/auth.php';
