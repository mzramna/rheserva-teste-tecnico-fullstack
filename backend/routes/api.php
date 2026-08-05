<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Support\Facades\Route;

// Rotas Públicas (Loja, Catálogo e Checkout de Compras)
Route::get('/produtos', [ProdutoController::class, 'index']);
Route::get('/produtos/{produto}', [ProdutoController::class, 'show']);
Route::post('/checkout', [CheckoutController::class, 'process']);
Route::post('/login', [AuthController::class, 'login']);

// Rotas Protegidas (Exigem Autenticação de Admin via Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/produtos', [ProdutoController::class, 'store']);
    Route::put('/produtos/{produto}', [ProdutoController::class, 'update']);
    Route::delete('/produtos/{produto}', [ProdutoController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});
