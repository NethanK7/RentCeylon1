<?php

namespace App\Http\Controllers\Lister;

use App\Enums\BookingStatus;
use App\Enums\DepositStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Lister: Incoming Bookings (Page 16). Manage active rentals and confirm
 * returns — the only lister-initiated path that releases an escrow deposit
 * (Constraint 03: deposit release is always manual).
 */
class BookingManageController extends Controller
{
    public function index(Request $request): Response
    {
        $bookings = Booking::where('lister_id', $request->user()->id)
            ->with(['listing.photos', 'renter:id,name,phone', 'deposit'])
            ->whereNotIn('status', [BookingStatus::Cancelled->value, BookingStatus::NoShow->value])
            ->latest()
            ->get()
            ->map(fn (Booking $b) => [
                'id' => $b->id,
                'reference' => $b->reference,
                'status' => $b->status->value,
                'status_label' => $b->status->label(),
                'start_date' => $b->start_date->toDateString(),
                'end_date' => $b->end_date->toDateString(),
                'total' => $b->total,
                'deposit_amount' => $b->deposit_amount,
                'deposit_status' => $b->deposit?->status->value,
                'currency' => $b->currency,
                'renter' => ['name' => $b->renter->name, 'phone' => $b->phone_revealed ? $b->renter->phone : null],
                'listing' => [
                    'title' => $b->listing->title,
                    'photo' => $b->listing->photos->first() ? $this->url($b->listing->photos->first()->path) : null,
                ],
                'has_pickup_photos' => $b->hasPickupPhotos(),
                'has_return_photos' => $b->hasReturnPhotos(),
                'can_confirm_return' => $b->status === BookingStatus::Returned,
            ]);

        return Inertia::render('Lister/Bookings/Index', ['bookings' => $bookings]);
    }

    /**
     * Confirm item returned → releases the escrow deposit to the lister.
     * Only valid once the renter has uploaded return photos and the booking
     * is in the Returned state (hard gate — Constraint 02).
     */
    public function confirmReturn(Request $request, Booking $booking)
    {
        abort_unless($booking->lister_id === $request->user()->id, 403);

        if ($booking->status !== BookingStatus::Returned || ! $booking->hasReturnPhotos()) {
            return back()->with('error', 'Return photos must be uploaded before you can confirm the return.');
        }

        $booking->deposit?->update([
            'status' => DepositStatus::ReleasedToLister->value,
            'amount_to_lister' => $booking->deposit->amount,
            'release_channel' => 'lister_confirm',
            'released_by' => $request->user()->id,
            'released_at' => now(),
        ]);

        $booking->update([
            'status' => BookingStatus::Closed->value,
            'completed_at' => now(),
            'closed_at' => now(),
        ]);

        $booking->listing()->increment('bookings_count');

        return back()->with('success', 'Return confirmed — deposit released. Thanks!');
    }

    private function url(string $path): string
    {
        return str_starts_with($path, 'http') ? $path : Storage::disk('public')->url($path);
    }
}
