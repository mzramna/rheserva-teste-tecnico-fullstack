<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Support\Facades\Route;

Route::get('/produtos', [ProdutoController::class, 'index']);
Route::post('/checkout', [CheckoutController::class, 'process']);
