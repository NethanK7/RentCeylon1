<?php

namespace App\Http\Controllers\Lister;

use App\Enums\BookingStatus;
use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $pendingEarnings = Booking::where('lister_id', $user->id)
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Active->value, BookingStatus::Returned->value])
            ->sum('subtotal');

        $paidOut = Booking::where('lister_id', $user->id)
            ->where('status', BookingStatus::Closed->value)
            ->sum('subtotal');

        return Inertia::render('Lister/Dashboard', [
            'stats' => [
                'active_listings' => $user->listings()->where('status', ListingStatus::Active->value)->count(),
                'booking_requests' => Booking::where('lister_id', $user->id)
                    ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Active->value])
                    ->count(),
                'pending_earnings' => $pendingEarnings,
                'paid_out' => $paidOut,
            ],
        ]);
    }
}
