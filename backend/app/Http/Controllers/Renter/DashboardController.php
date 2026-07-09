<?php

namespace App\Http\Controllers\Renter;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/** Renter: "My rentals" (Page 10/11/12 combined dashboard entry). */
class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $bookings = Booking::where('renter_id', $request->user()->id)
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

        $active = $bookings->whereIn('status', [
            BookingStatus::Confirmed->value, BookingStatus::Active->value,
            BookingStatus::AwaitingReturn->value, BookingStatus::Returned->value,
        ])->values();
        $history = $bookings->whereIn('status', [
            BookingStatus::Closed->value, BookingStatus::Cancelled->value, BookingStatus::NoShow->value,
        ])->values();

        return Inertia::render('Renter/Dashboard', [
            'active' => $active,
            'history' => $history,
        ]);
    }

    private function url(string $path): string
    {
        return str_starts_with($path, 'http') ? $path : Storage::disk('public')->url($path);
    }
}
