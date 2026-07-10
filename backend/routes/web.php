<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ConditionPhotoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Lister\BookingManageController;
use App\Http\Controllers\Lister\DashboardController as ListerDashboardController;
use App\Http\Controllers\Lister\ListingManageController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Renter\DashboardController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Public / Pre-login ──
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/browse', [ListingController::class, 'index'])->name('browse');
Route::get('/listings/{listing:slug}', [ListingController::class, 'show'])->name('listings.show');

// Static trust pages (Pages 04, 05, 24)
Route::get('/pricing', fn () => Inertia::render('Pricing'))->name('pricing');
Route::get('/trust', fn () => Inertia::render('Trust'))->name('trust');
Route::get('/property-management', fn () => Inertia::render('PropertyManagement/Landing'))->name('pm.landing');

// ── Authenticated ──
Route::middleware('auth')->group(function () {
    // Checkout + bookings (Pages 09, 10, 11, 23)
    Route::get('/listings/{listing:slug}/checkout', [BookingController::class, 'checkout'])->name('bookings.checkout');
    Route::post('/listings/{listing:slug}/checkout', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/photos', [ConditionPhotoController::class, 'store'])->name('bookings.photos.store');

    // ID verification (Page 08) — stub screen for now, full flow next.
    Route::get('/verify-id', fn () => Inertia::render('Verification/Id', [
        'status' => request()->user()->id_verification_status->value,
    ]))->name('verification.id.show');

    // Role dashboards
    Route::get('/dashboard', function () {
        $user = request()->user();
        return match ($user->role->value) {
            'lister' => redirect()->route('lister.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            'manager' => redirect()->route('manager.dashboard'),
            default => redirect()->route('renter.dashboard'),
        };
    })->name('dashboard');

    Route::get('/rentals', [DashboardController::class, 'index'])->name('renter.dashboard');

    // Wishlist (Airbnb-style save/heart)
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/listings/{listing}/wishlist', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Lister flow (Pages 13–17)
    Route::middleware('role:lister')->prefix('lister')->name('lister.')->group(function () {
        Route::get('/', [ListerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/listings', [ListingManageController::class, 'index'])->name('listings.index');
        Route::get('/listings/create', [ListingManageController::class, 'create'])->name('listings.create');
        Route::post('/listings', [ListingManageController::class, 'store'])->name('listings.store');
        Route::get('/listings/{listing}/edit', [ListingManageController::class, 'edit'])->name('listings.edit');
        Route::post('/listings/{listing}', [ListingManageController::class, 'update'])->name('listings.update');
        Route::post('/listings/{listing}/pause', [ListingManageController::class, 'pause'])->name('listings.pause');
        Route::post('/listings/{listing}/activate', [ListingManageController::class, 'activate'])->name('listings.activate');
        Route::delete('/listings/{listing}', [ListingManageController::class, 'destroy'])->name('listings.destroy');

        Route::get('/bookings', [BookingManageController::class, 'index'])->name('bookings.index');
        Route::post('/bookings/{booking}/confirm-return', [BookingManageController::class, 'confirmReturn'])->name('bookings.confirm-return');
    });

    Route::get('/admin', fn () => Inertia::render('Admin/Dashboard'))->middleware('role:admin')->name('admin.dashboard');
    Route::get('/manager', fn () => Inertia::render('Manager/Dashboard'))->middleware('role:manager')->name('manager.dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
