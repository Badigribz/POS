<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\MpesaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index']); // For cashier to list products
    Route::post('/sales', [SaleController::class, 'store']); // To record sales
    // Route::get('/sales', [SaleController::class, 'report']);
});
 Route::get('/sales', [SaleController::class, 'report']);
 Route::post('/mpesa/stkpush', [MpesaController::class, 'stkPush']);
 Route::post('/mpesa/callback', [MpesaController::class, 'callback']);
// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::get('/sales/report', [SaleController::class, 'report']);
// });
