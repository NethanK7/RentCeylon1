<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingOverviewController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with(['listing:id,title,slug', 'renter:id,name', 'lister:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where('reference', 'like', "%{$request->q}%"))
            ->latest()
            ->paginate(30)
            ->withQueryString()
            ->through(fn (Booking $b) => [
                'id'         => $b->id,
                'reference'  => $b->reference,
                'status'     => $b->status->value,
                'listing'    => ['title' => $b->listing->title, 'slug' => $b->listing->slug],
                'renter'     => $b->renter->name,
                'lister'     => $b->lister->name,
                'start_date' => $b->start_date->format('d M Y'),
                'end_date'   => $b->end_date->format('d M Y'),
                'total'      => $b->total,
                'currency'   => $b->currency,
                'created_at' => $b->created_at->format('d M Y'),
            ]);

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'filters'  => $request->only(['status', 'q']),
        ]);
    }
}
