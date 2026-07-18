<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\MessageApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\ReviewApiController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

/*
| RentCeylon mobile API (Flutter) — Sanctum token auth.
| Base path: /api
*/

// ── Public ─────────────────────────────────────────────────────────────────
Route::post('/register',      [AuthController::class, 'register']);
Route::post('/login',         [AuthController::class, 'login']);
Route::post('/auth/google',   [GoogleAuthController::class, 'login']);

Route::get('/categories',                      [CatalogController::class, 'categories']);
Route::get('/listings',                        [CatalogController::class, 'listings']);
Route::get('/listings/{listing:slug}',         [CatalogController::class, 'listing']);
Route::get('/listings/{listing:slug}/reviews', [ReviewApiController::class, 'listing']);

// ── Authenticated (Bearer token) ────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::patch('/me',         [ProfileApiController::class, 'update']);
    Route::post('/me/password', [ProfileApiController::class, 'password']);

    // Wishlist
    Route::get('/wishlist',                              [WishlistController::class, 'index']);
    Route::post('/listings/{listing}/wishlist',          [WishlistController::class, 'toggle']);

    // Listings — availability / quote before booking
    Route::get('/listings/{listing:slug}/quote', [BookingApiController::class, 'quote']);

    // Bookings
    Route::get('/bookings',                   [BookingApiController::class, 'index']);
    Route::post('/bookings',                  [BookingApiController::class, 'store']);
    Route::get('/bookings/{booking}',         [BookingApiController::class, 'show']);
    Route::post('/bookings/{booking}/cancel', [BookingApiController::class, 'cancel']);
    Route::get('/bookings/{booking}/qr',      [BookingApiController::class, 'qr'])->name('api.bookings.qr');

    // Reviews
    Route::post('/reviews', [ReviewApiController::class, 'store']);

    // Messaging
    Route::get('/threads',                    [MessageApiController::class, 'threads']);
    Route::post('/threads',                   [MessageApiController::class, 'startThread']);
    Route::get('/threads/{thread}/messages',  [MessageApiController::class, 'messages']);
    Route::post('/threads/{thread}/messages', [MessageApiController::class, 'send']);
});
