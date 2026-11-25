<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentExceptionController;
use App\Http\Controllers\TrackingEventController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('shipments', ShipmentController::class);

    Route::post('/shipments/{shipment}/tracking', [TrackingEventController::class, 'store'])
        ->name('shipments.tracking.store');

    Route::post('/shipments/{shipment}/exceptions', [ShipmentExceptionController::class, 'store'])
        ->name('shipments.exceptions.store');
    Route::patch('/exceptions/{shipmentException}', [ShipmentExceptionController::class, 'update'])
        ->name('exceptions.update');

    // Pengaturan threshold delay: hanya Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';