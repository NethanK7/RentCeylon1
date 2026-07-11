<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\ListingStatus;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ListingModerationController extends Controller
{
    public function index(Request $request)
    {
        $listings = Listing::withTrashed()
            ->with(['lister:id,name,email', 'category:id,name', 'photos'])
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', "%{$request->q}%"))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Listing $l) => [
                'id'         => $l->id,
                'title'      => $l->title,
                'slug'       => $l->slug,
                'status'     => $l->status->value,
                'daily_rate' => $l->daily_rate,
                'city'       => $l->city,
                'category'   => $l->category->name,
                'lister'     => ['id' => $l->lister->id, 'name' => $l->lister->name],
                'photo'      => $l->photos->first() ? $this->photoUrl($l->photos->first()->path) : null,
                'created_at' => $l->created_at->format('d M Y'),
                'deleted'    => $l->trashed(),
            ]);

        return Inertia::render('Admin/Listings/Index', [
            'listings' => $listings,
            'filters'  => $request->only(['q', 'status']),
        ]);
    }

    public function remove(Request $request, Listing $listing)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $listing->update(['status' => ListingStatus::Removed->value]);
        $listing->delete();
        return back()->with('success', 'Listing removed.');
    }

    public function restore(Listing $listing)
    {
        $listing->restore();
        $listing->update(['status' => ListingStatus::Active->value]);
        return back()->with('success', 'Listing restored.');
    }

    private function photoUrl(string $path): string
    {
        return str_starts_with($path, 'http') ? $path : Storage::disk('public')->url($path);
    }
}
