<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
// Core Transaction stubs handled by DashboardController
    Route::get('/purchase-orders', [DashboardController::class, 'purchaseOrders'])->name('purchase-orders.index');
    Route::get('/back-orders', [DashboardController::class, 'backOrders'])->name('back-orders.index');
    Route::get('/receiving', [DashboardController::class, 'receiving'])->name('receiving.index');
    Route::get('/stocks', [DashboardController::class, 'stocks'])->name('stocks.index');
    Route::get('/sales', [DashboardController::class, 'sales'])->name('sales.index');

    // Maintenance stubs handled by DashboardController
    Route::get('/suppliers', [DashboardController::class, 'suppliers'])->name('suppliers.index');
    Route::get('/items', [DashboardController::class, 'items'])->name('items.index');
    Route::get('/users', [DashboardController::class, 'users'])->name('users.index');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings.index');


});

require __DIR__.'/auth.php'; 