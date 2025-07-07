<?php

use App\Http\Controllers\Api\CryptocurrencyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Cryptocurrency API Routes
Route::prefix('crypto')->group(function () {
    // Get list of cryptocurrencies (paginated)
    Route::get('/', [CryptocurrencyController::class, 'index']);
    
    // Get top cryptocurrencies by market cap
    Route::get('/top', [CryptocurrencyController::class, 'top']);
    
    // Search cryptocurrencies
    Route::get('/search', [CryptocurrencyController::class, 'search']);
    
    // Get cryptocurrency statistics
    Route::get('/stats', [CryptocurrencyController::class, 'stats']);
    
    // Get specific cryptocurrency by coin_id
    Route::get('/{coinId}', [CryptocurrencyController::class, 'show']);
});
