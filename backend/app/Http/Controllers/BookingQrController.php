<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BookingQrController extends Controller
{
    /** Auth-protected QR shown on the booking page (SVG is fine for browser). */
    public function show(Request $request, Booking $booking)
    {
        abort_unless($booking->renter_id === $request->user()->id, 403);

        $scanUrl = URL::signedRoute('bookings.scan', ['booking' => $booking->id], now()->addHours(24));
        $svg     = QrCode::format('svg')->size(200)->generate($scanUrl);

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Public PNG endpoint used inside emails.
     * The URL itself is signed so it can't be guessed.
     * Returns a PNG so Gmail/Outlook load it as a regular <img>.
     */
    public function emailImage(Request $request, Booking $booking)
    {
        abort_unless($request->hasValidSignature(), 403);

        $scanUrl = URL::signedRoute('bookings.scan', ['booking' => $booking->id], now()->addHours(48));
        $png     = QrCode::format('png')->size(280)->margin(1)->generate($scanUrl);

        return response($png)->header('Content-Type', 'image/png');
    }

    /** Page the lister sees after scanning the QR code. */
    public function scan(Request $request, Booking $booking)
    {
        abort_unless($request->hasValidSignature(), 403);

        $booking->load(['listing:id,title,city,daily_rate,currency', 'renter:id,name,email,phone', 'deposit']);

        return Inertia::render('Bookings/Scan', [
            'booking' => [
                'id'             => $booking->id,
                'reference'      => $booking->reference,
                'status'         => $booking->status->value,
                'status_label'   => $booking->status->label(),
                'start_date'     => $booking->start_date->format('d M Y'),
                'end_date'       => $booking->end_date->format('d M Y'),
                'days'           => $booking->days,
                'total'          => $booking->total,
                'deposit_amount' => $booking->deposit_amount,
                'currency'       => $booking->currency,
                'listing'        => [
                    'title'      => $booking->listing->title,
                    'city'       => $booking->listing->city,
                    'daily_rate' => $booking->listing->daily_rate,
                ],
                'renter'         => [
                    'name'  => $booking->renter->name,
                    'email' => $booking->renter->email,
                    'phone' => $booking->phone_revealed ? $booking->renter->phone : null,
                ],
                'deposit_status' => $booking->deposit?->status->value ?? null,
            ],
        ]);
    }
}
