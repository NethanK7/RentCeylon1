<?php

use App\Http\Controllers\Admin\BookingOverviewController;
use App\Http\Controllers\BookingQrController;
use App\Http\Controllers\VerificationSubmitController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ListingModerationController;
use App\Http\Controllers\Admin\ReviewModerationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VerificationController;
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

    // ID verification
    Route::get('/verify-id', fn () => Inertia::render('Verification/Id', [
        'status' => request()->user()->id_verification_status->value,
    ]))->name('verification.id.show');
    Route::post('/verify-id', [VerificationSubmitController::class, 'store'])->name('verification.id.submit');

    // Booking QR
    Route::get('/bookings/{booking}/qr', [BookingQrController::class, 'show'])->name('bookings.qr');

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

    // Admin panel
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/unsuspend', [AdminUserController::class, 'unsuspend'])->name('users.unsuspend');
        Route::post('/users/{user}/role', [AdminUserController::class, 'changeRole'])->name('users.role');

        Route::get('/verifications', [VerificationController::class, 'index'])->name('verifications.index');
        Route::post('/verifications/{verification}/approve', [VerificationController::class, 'approve'])->name('verifications.approve');
        Route::post('/verifications/{verification}/reject', [VerificationController::class, 'reject'])->name('verifications.reject');

        Route::get('/listings', [ListingModerationController::class, 'index'])->name('listings.index');
        Route::post('/listings/{listing}/remove', [ListingModerationController::class, 'remove'])->name('listings.remove');
        Route::post('/listings/{listing}/restore', [ListingModerationController::class, 'restore'])->name('listings.restore');

        Route::get('/bookings', [BookingOverviewController::class, 'index'])->name('bookings.index');

        Route::get('/reviews', [ReviewModerationController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{review}/hide', [ReviewModerationController::class, 'hide'])->name('reviews.hide');
        Route::post('/reviews/{review}/keep', [ReviewModerationController::class, 'keep'])->name('reviews.keep');
    });

    Route::get('/manager', fn () => Inertia::render('Manager/Dashboard'))->middleware('role:manager')->name('manager.dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// QR scan + email image — signed URLs, no auth required
Route::get('/bookings/{booking}/scan',       [BookingQrController::class, 'scan'])->name('bookings.scan');
Route::get('/bookings/{booking}/qr-email',   [BookingQrController::class, 'emailImage'])->name('bookings.qr.email');

require __DIR__.'/auth.php';
