<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        // Only renter or lister of this booking can raise a dispute
        $user = $request->user();
        if ($booking->renter_id !== $user->id && $booking->listing->user_id !== $user->id) {
            abort(403);
        }
        // Only allow disputes on active/returned/closed bookings
        if (!in_array($booking->status->value, ['active', 'returned', 'closed', 'confirmed'])) {
            return back()->with('error', 'Disputes can only be raised on active or completed bookings.');
        }
        // Only one open dispute per booking
        if ($booking->disputes()->where('status', 'open')->exists()) {
            return back()->with('error', 'There is already an open dispute for this booking.');
        }

        $request->validate([
            'type'        => 'required|in:damage,no_return,wrong_item,other',
            'description' => 'required|string|min:20|max:2000',
        ]);

        $booking->disputes()->create([
            'raised_by'    => $user->id,
            'type'         => $request->type,
            'description'  => $request->description,
            'status'       => 'open',
            'sla_deadline' => now()->addHours(72),
        ]);

        return back()->with('success', 'Dispute raised. Admin will review within 72 hours.');
    }
}
