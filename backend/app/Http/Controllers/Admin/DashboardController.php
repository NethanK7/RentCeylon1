<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dispute;
use App\Models\IdVerification;
use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users'             => User::count(),
                'listings'          => Listing::count(),
                'bookings'          => Booking::count(),
                'pending_verifications' => IdVerification::where('status', 'pending')->count(),
                'open_disputes'     => Dispute::whereNotIn('status', ['resolved', 'closed'])->count(),
                'flagged_reviews'   => Review::where('is_flagged', true)->where('is_visible', true)->count(),
                'active_bookings'   => Booking::whereNotIn('status', ['closed', 'cancelled', 'no_show'])->count(),
            ],
            'recent_users' => User::latest()->limit(5)->get(['id', 'name', 'email', 'role', 'id_verification_status', 'created_at']),
        ]);
    }
}
