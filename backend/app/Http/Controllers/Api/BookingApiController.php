<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Mobile "Trips" tab — the renter's bookings, mirroring the web /rentals page. */
class BookingApiController extends Controller
{
    public function mine(Request $request)
    {
        return Booking::where('renter_id', $request->user()->id)
            ->with(['listing.photos', 'lister:id,name'])
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
                'currency' => $b->currency,
                'listing' => [
                    'title' => $b->listing->title,
                    'photo' => $b->listing->photos->first() ? $this->url($b->listing->photos->first()->path) : null,
                ],
                'lister' => $b->lister->name,
            ]);
    }

    private function url(string $path): string
    {
        return str_starts_with($path, 'http') ? $path : Storage::disk('public')->url($path);
    }
}
