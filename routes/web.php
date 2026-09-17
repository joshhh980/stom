<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('/', [Controllers\DashboardController::class, 'index'])->name('dashboard');


    Route::resource('purchase-orders', Controllers\PurchaseOrderController::class);
    Route::post('purchase-orders/import', [Controllers\PurchaseOrderController::class, "import"])->name('purchase-orders.import');

    Route::resource('back-orders', Controllers\BackOrderController::class);
    Route::get('receivings/manage', [Controllers\ReceivingController::class, 'manage'])->name('receivings.manage');
    Route::resource('receivings', Controllers\ReceivingController::class);

    Route::resource('stocks', Controllers\StockController::class)->only(['index', 'store']);
    Route::get('stocks/manage', [Controllers\StockController::class, 'createOrEdit'])->name('stocks.manage');
    Route::post('stocks/import', [Controllers\StockController::class, "import"])->name('stocks.import');

    Route::resource('sales', Controllers\SaleController::class);
    Route::get('sales/manage', [Controllers\SaleController::class, 'createOrEdit'])->name('sales.manage');

    // Maintenance stubs handled by Controllers\DashboardController
    Route::get('/suppliers', [Controllers\DashboardController::class, 'suppliers'])->name('suppliers.index');
    Route::resource('/items', Controllers\DashboardController::class);
    Route::get('/users', [Controllers\DashboardController::class, 'users'])->name('users.index');
    Route::get('/settings', [Controllers\DashboardController::class, 'settings'])->name('settings.index');
});

require __DIR__ . '/auth.php';
