<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('/', [Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [Controllers\ProfileController::class, 'update'])->name('profile.update');


    Route::resource('purchase-orders', Controllers\PurchaseOrderController::class);
    Route::post('purchase-orders/import', [Controllers\PurchaseOrderController::class, "import"])->name('purchase-orders.import');

    Route::resource('back-orders', Controllers\BackOrderController::class);
    Route::get('receivings/manage', [Controllers\ReceivingController::class, 'manage'])->name('receivings.manage');
    Route::resource('receivings', Controllers\ReceivingController::class);

    Route::resource('stocks', Controllers\StockController::class)->only(['index', 'store']);
    Route::get('stocks/manage', [Controllers\StockController::class, 'createOrEdit'])->name('stocks.manage');
    Route::post('stocks/import', [Controllers\StockController::class, "import"])->name('stocks.import');


    Route::get('sales/manage/{id?}', [Controllers\SaleController::class, 'createOrEdit'])->name('sales.manage');
    Route::resource('sales', Controllers\SaleController::class);

    Route::resource('suppliers', Controllers\SupplierController::class);
    Route::resource('items', Controllers\ItemController::class);
    Route::resource('users', Controllers\UserController::class);
    Route::get('/audit-logs', [Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/settings', [Controllers\SystemSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [Controllers\SystemSettingsController::class, 'update'])->name('settings.update');
});

require __DIR__ . '/auth.php';
