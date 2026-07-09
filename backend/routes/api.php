<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use Illuminate\Support\Facades\Route;

/*
| RentCeylon mobile API (Flutter) — Sanctum token auth.
| Base path: /api
*/

// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/listings', [CatalogController::class, 'listings']);
Route::get('/listings/{listing:slug}', [CatalogController::class, 'listing']);

// Authenticated (Bearer token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
