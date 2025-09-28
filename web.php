<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SellController;
use App\Http\Controllers\CarPartController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DashboardController;

// Set the dashboard as the default landing page
Route::get('/', function () {
    return redirect()->route('dashboard.index');
});

// Vehicles
Route::resource('vehicles', VehicleController::class);

// Sell
Route::resource('sell', SellController::class);

// Services (custom routes)
Route::get('/vehicles/{vehicle}/services', [ServiceController::class, 'manage'])
    ->name('services.manage');
Route::post('/vehicles/{vehicle}/services', [ServiceController::class, 'store'])
    ->name('services.store');
Route::put('/services/{service}', [ServiceController::class, 'update'])
    ->name('services.update');

// PDF download
Route::get(
    '/vehicles/{vehicle}/services/{date}/download-pdf',
    [ServiceController::class, 'downloadPdf']
)->name('services.downloadPdf');

// Services resource
Route::resource('services', ServiceController::class)->except([
    'index',
    'show',
    'create',
    'edit'
]);

// routes/web.php
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

Route::resource('inventory', CarPartController::class)->parameters([
    'inventory' => 'carPart'
]);

// ADD THIS DOWNLOAD ROUTE:
Route::get('/inventory/{carPart}/download', [CarPartController::class, 'download'])
    ->name('inventory.download');

Route::patch('/inventory/{carPart}/update-quantity', [CarPartController::class, 'updateQuantity'])->name('inventory.update-quantity');
Route::get('/inventory/{carPart}/download-pdf', [CarPartController::class, 'downloadPdf'])->name('inventory.download-pdf');
