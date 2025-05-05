<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\DiscountController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'getPurchaseOrders']);
});

Route::apiResource('zones', ZoneController::class);

// Discount API routes
Route::post('/discounts', [DiscountController::class, 'store']);
Route::put('/discounts/{id}', [DiscountController::class, 'update']);
Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);
Route::get('/discounts', [DiscountController::class, 'getDiscounts']);

