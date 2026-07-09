<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Listing;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    /** Checkout screen (Page 09) — shows fee tier + deposit before payment. */
    public function checkout(Request $request, Listing $listing)
    {
        abort_unless($listing->status->value === 'active', 404);

        $validated = $request->validate([
            'start' => 'required|date|after_or_equal:today',
            'end' => 'required|date|after_or_equal:start',
        ]);

        if (! $listing->isAvailableBetween($validated['start'], $validated['end'])) {
            return back()->with('error', 'Those dates are not available.');
        }

        $quote = $this->bookings->quote($listing, $validated['start'], $validated['end']);
        $listing->load('photos', 'lister:id,name');

        return Inertia::render('Bookings/Checkout', [
            'listing' => [
                'id' => $listing->id,
                'title' => $listing->title,
                'slug' => $listing->slug,
                'city' => $listing->city,
                'daily_rate' => $listing->daily_rate,
                'security_deposit' => $listing->security_deposit,
                'currency' => $listing->currency,
                'photo' => $listing->photos->first()
                    ? (str_starts_with($listing->photos->first()->path, 'http') ? $listing->photos->first()->path : asset('storage/'.$listing->photos->first()->path))
                    : null,
                'lister' => $listing->lister->name,
            ],
            'quote' => $quote,
            // Idempotency key minted server-side, submitted back on pay (Constraint 06).
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    /** Capture payment + create booking (idempotent). */
    public function store(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'start' => 'required|date|after_or_equal:today',
            'end' => 'required|date|after_or_equal:start',
            'gateway' => 'required|in:payhere,ipay,stripe',
            'idempotency_key' => 'required|uuid',
            'accept_policy' => 'accepted',
            'accept_agreement' => 'accepted',
        ]);

        $booking = $this->bookings->checkout(
            listing: $listing,
            renter: $request->user(),
            start: $validated['start'],
            end: $validated['end'],
            gateway: $validated['gateway'],
            idempotencyKey: $validated['idempotency_key'],
            acceptedPolicy: (bool) $validated['accept_policy'],
            acceptedAgreement: (bool) $validated['accept_agreement'],
        );

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking confirmed! Your deposit is held securely in escrow.');
    }

    /** Renter's booking / active rental view (Pages 10, 11). */
    public function show(Request $request, Booking $booking)
    {
        $userId = $request->user()->id;
        abort_unless($booking->renter_id === $userId || $booking->lister_id === $userId, 403);

        $booking->load(['listing.photos', 'lister:id,name,phone', 'renter:id,name,phone', 'deposit', 'conditionPhotos', 'rentalAgreement', 'cancellation']);

        $isRenter = $booking->renter_id === $userId;
        $daysUntilStart = today()->diffInDays($booking->start_date, false);
        $canCancel = $isRenter && $booking->status === BookingStatus::Confirmed && $daysUntilStart >= 0;

        return Inertia::render('Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'reference' => $booking->reference,
                'status' => $booking->status->value,
                'status_label' => $booking->status->label(),
                'start_date' => $booking->start_date->toDateString(),
                'end_date' => $booking->end_date->toDateString(),
                'days' => $booking->days,
                'subtotal' => $booking->subtotal,
                'platform_fee' => $booking->platform_fee,
                'fee_rate' => $booking->fee_rate,
                'deposit_amount' => $booking->deposit_amount,
                'total' => $booking->total,
                'currency' => $booking->currency,
                'phone_revealed' => $booking->phone_revealed,
                'deposit_status' => $booking->deposit?->status->value,
                'listing' => [
                    'title' => $booking->listing->title,
                    'photo' => $booking->listing->photos->first()
                        ? (str_starts_with($booking->listing->photos->first()->path, 'http') ? $booking->listing->photos->first()->path : asset('storage/'.$booking->listing->photos->first()->path))
                        : null,
                ],
                'lister' => [
                    'name' => $booking->lister->name,
                    // Phone only after payment confirmed (Constraint 04).
                    'phone' => $booking->phone_revealed ? $booking->lister->phone : null,
                ],
                'is_renter' => $isRenter,
                'has_pickup_photos' => $booking->hasPickupPhotos(),
                'has_return_photos' => $booking->hasReturnPhotos(),
                'can_cancel' => $canCancel,
                'cancellation' => $booking->cancellation ? [
                    'tier' => $booking->cancellation->tier,
                    'rental_refund' => $booking->cancellation->rental_refund,
                    'deposit_refund' => $booking->cancellation->deposit_refund,
                    'lister_compensation' => $booking->cancellation->lister_compensation,
                ] : null,
            ],
        ]);
    }

    /** Pre-pickup cancellation with tiered refund (Page 23). */
    public function cancel(Request $request, Booking $booking)
    {
        abort_unless($booking->renter_id === $request->user()->id, 403);

        $validated = $request->validate(['reason' => 'nullable|string|max:500']);

        $this->bookings->cancel($booking, $request->user(), $validated['reason'] ?? null);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking cancelled. Your refund has been processed.');
    }
}
