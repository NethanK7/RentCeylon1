<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Listing;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BookingApiController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    /** List the authenticated user's bookings (as renter or lister). */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $request->query('role', 'renter'); // ?role=lister for lister view

        $query = $role === 'lister'
            ? Booking::where('lister_id', $user->id)
            : Booking::where('renter_id', $user->id);

        return $query
            ->with(['listing:id,title,slug,city,daily_rate,currency', 'listing.photos'])
            ->latest()
            ->paginate(20)
            ->through(fn (Booking $b) => $this->summary($b, $role));
    }

    /** Create (checkout) a new booking. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_slug'     => 'required|string|exists:listings,slug',
            'start'            => 'required|date|after_or_equal:today',
            'end'              => 'required|date|after_or_equal:start',
            'gateway'          => 'required|in:payhere,ipay,stripe',
            'idempotency_key'  => 'required|uuid',
            'accept_policy'    => 'accepted',
            'accept_agreement' => 'accepted',
        ]);

        $listing = Listing::where('slug', $validated['listing_slug'])->firstOrFail();

        $booking = $this->bookings->checkout(
            listing: $listing,
            renter: $request->user(),
            start: $validated['start'],
            end: $validated['end'],
            gateway: $validated['gateway'],
            idempotencyKey: $validated['idempotency_key'],
            acceptedPolicy: true,
            acceptedAgreement: true,
        );

        return response()->json($this->detail($booking, 'renter'), 201);
    }

    /** Show a single booking — accessible by renter or lister. */
    public function show(Request $request, Booking $booking)
    {
        $user = $request->user();
        abort_unless($booking->renter_id === $user->id || $booking->lister_id === $user->id, 403);

        $booking->load(['listing.photos', 'lister:id,name,phone', 'renter:id,name,phone', 'deposit', 'cancellation']);
        $role = $booking->renter_id === $user->id ? 'renter' : 'lister';

        return $this->detail($booking, $role);
    }

    /** Cancel a booking (renter only). */
    public function cancel(Request $request, Booking $booking)
    {
        abort_unless($booking->renter_id === $request->user()->id, 403);

        $validated = $request->validate(['reason' => 'nullable|string|max:500']);

        $cancellation = $this->bookings->cancel($booking, $request->user(), $validated['reason'] ?? null);

        return response()->json([
            'message'             => 'Booking cancelled.',
            'tier'                => $cancellation->tier,
            'rental_refund'       => $cancellation->rental_refund,
            'deposit_refund'      => $cancellation->deposit_refund,
            'lister_compensation' => $cancellation->lister_compensation,
        ]);
    }

    /** QR code PNG for renter — embed in Flutter Image.network or share widget. */
    public function qr(Request $request, Booking $booking)
    {
        abort_unless($booking->renter_id === $request->user()->id, 403);

        $scanUrl = URL::signedRoute('bookings.scan', ['booking' => $booking->id], now()->addHours(24));
        $png = QrCode::format('png')->size(280)->margin(1)->generate($scanUrl);

        return response($png)->header('Content-Type', 'image/png');
    }

    /** Quote dates before committing — returns fee breakdown. */
    public function quote(Request $request, Listing $listing)
    {
        abort_unless($listing->status->value === 'active', 404);

        $validated = $request->validate([
            'start' => 'required|date|after_or_equal:today',
            'end'   => 'required|date|after_or_equal:start',
        ]);

        if (! $listing->isAvailableBetween($validated['start'], $validated['end'])) {
            return response()->json(['available' => false, 'message' => 'Dates not available.'], 422);
        }

        return array_merge($this->bookings->quote($listing, $validated['start'], $validated['end']), [
            'available' => true,
        ]);
    }

    private function summary(Booking $b, string $role): array
    {
        $photo = $b->listing->photos->first();
        return [
            'id'           => $b->id,
            'reference'    => $b->reference,
            'status'       => $b->status->value,
            'status_label' => $b->status->label(),
            'start_date'   => $b->start_date->toDateString(),
            'end_date'     => $b->end_date->toDateString(),
            'days'         => $b->days,
            'total'        => $b->total,
            'currency'     => $b->currency,
            'listing' => [
                'title' => $b->listing->title,
                'city'  => $b->listing->city,
                'photo' => $photo ? (str_starts_with($photo->path, 'http') ? $photo->path : asset('storage/'.$photo->path)) : null,
            ],
        ];
    }

    private function detail(Booking $b, string $role): array
    {
        $photo = $b->listing->photos->first();
        return [
            'id'               => $b->id,
            'reference'        => $b->reference,
            'status'           => $b->status->value,
            'status_label'     => $b->status->label(),
            'start_date'       => $b->start_date->toDateString(),
            'end_date'         => $b->end_date->toDateString(),
            'days'             => $b->days,
            'daily_rate'       => $b->daily_rate,
            'subtotal'         => $b->subtotal,
            'platform_fee'     => $b->platform_fee,
            'deposit_amount'   => $b->deposit_amount,
            'total'            => $b->total,
            'currency'         => $b->currency,
            'phone_revealed'   => $b->phone_revealed,
            'has_pickup_photos' => $b->hasPickupPhotos(),
            'has_return_photos' => $b->hasReturnPhotos(),
            'deposit_status'   => $b->deposit?->status->value,
            'listing' => [
                'title'      => $b->listing->title,
                'slug'       => $b->listing->slug,
                'city'       => $b->listing->city,
                'daily_rate' => $b->listing->daily_rate,
                'photo'      => $photo ? (str_starts_with($photo->path, 'http') ? $photo->path : asset('storage/'.$photo->path)) : null,
            ],
            'lister' => [
                'name'  => $b->lister->name,
                'phone' => $b->phone_revealed ? $b->lister->phone : null,
            ],
            'renter' => [
                'name'  => $b->renter->name,
                'phone' => $b->phone_revealed && $role === 'lister' ? $b->renter->phone : null,
            ],
            'cancellation' => $b->cancellation ? [
                'tier'                => $b->cancellation->tier,
                'rental_refund'       => $b->cancellation->rental_refund,
                'deposit_refund'      => $b->cancellation->deposit_refund,
                'lister_compensation' => $b->cancellation->lister_compensation,
            ] : null,
            'qr_url' => $role === 'renter' && $b->status->value === 'confirmed'
                ? route('api.bookings.qr', $b->id)
                : null,
        ];
    }
}
