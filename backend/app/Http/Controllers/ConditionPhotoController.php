<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\ConditionPhoto;
use Illuminate\Http\Request;

/**
 * Condition-photo hard gate (Global Constraint 02): no transaction can
 * progress past pickup/return without a timestamped photo from that phase.
 * Uploading the first pickup photo advances Confirmed → Active; uploading
 * the first return photo advances Active → Returned (which then unlocks the
 * lister's "Confirm Return" action that releases the deposit).
 */
class ConditionPhotoController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        $user = $request->user();
        abort_unless(in_array($user->id, [$booking->renter_id, $booking->lister_id], true), 403);

        $validated = $request->validate([
            'phase' => 'required|in:pickup,return',
            'photo' => 'required|image|max:8192',
        ]);

        if ($validated['phase'] === 'pickup' && $booking->status !== BookingStatus::Confirmed) {
            return back()->with('error', 'Pickup photos can only be added once the booking is confirmed.');
        }
        if ($validated['phase'] === 'return' && ! in_array($booking->status, [BookingStatus::Active, BookingStatus::AwaitingReturn], true)) {
            return back()->with('error', 'Return photos can only be added while the rental is active.');
        }

        $path = $request->file('photo')->store("condition-photos/{$booking->id}", 'public');

        ConditionPhoto::create([
            'booking_id' => $booking->id,
            'uploaded_by' => $user->id,
            'phase' => $validated['phase'],
            'path' => $path,
            'taken_at' => now(),
            'upload_status' => 'uploaded',
        ]);

        if ($validated['phase'] === 'pickup' && $booking->status === BookingStatus::Confirmed) {
            $booking->update(['status' => BookingStatus::Active->value, 'started_at' => now()]);
        }

        if ($validated['phase'] === 'return' && $booking->hasReturnPhotos()) {
            $booking->update(['status' => BookingStatus::Returned->value, 'returned_at' => now()]);
        }

        return back()->with('success', $validated['phase'] === 'pickup'
            ? 'Pickup photo uploaded — rental is now active.'
            : 'Return photo uploaded. Waiting for the lister to confirm and release your deposit.');
    }
}
