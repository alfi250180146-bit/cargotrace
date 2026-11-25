<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrierController as ApiCarrierController;
use App\Http\Controllers\Api\LocationController as ApiLocationController;
use App\Http\Controllers\Api\ShipmentController as ApiShipmentController;
use Illuminate\Support\Facades\Route;

// Semua rute di file ini otomatis berawalan /api
Route::name('api.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        Route::apiResource('shipments', ApiShipmentController::class);

        Route::get('/shipments/{shipment}/tracking', [ApiShipmentController::class, 'tracking']);
        Route::post('/shipments/{shipment}/tracking', [ApiShipmentController::class, 'addTracking']);

        Route::get('/shipments/{shipment}/exceptions', [ApiShipmentController::class, 'exceptions']);
        Route::post('/shipments/{shipment}/exceptions', [ApiShipmentController::class, 'addException']);
        Route::patch('/exceptions/{shipmentException}', [ApiShipmentController::class, 'updateException']);

        Route::get('/carriers', [ApiCarrierController::class, 'index']);
        Route::get('/locations', [ApiLocationController::class, 'index']);

        // Hanya Admin yang boleh menambah master data
        Route::middleware('role:admin')->group(function () {
            Route::post('/carriers', [ApiCarrierController::class, 'store']);
            Route::post('/locations', [ApiLocationController::class, 'store']);
        });
    });
});