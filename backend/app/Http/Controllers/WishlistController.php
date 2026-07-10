<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Saved listings — the Airbnb heart/save feature. */
class WishlistController extends Controller
{
    public function index(Request $request): Response
    {
        $listings = Listing::public()
            ->whereHas('wishlistedBy', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with(['photos', 'category', 'badges.badge'])
            ->get()
            ->map(fn (Listing $l) => [
                'id' => $l->id,
                'title' => $l->title,
                'slug' => $l->slug,
                'daily_rate' => $l->daily_rate,
                'currency' => $l->currency,
                'city' => $l->city,
                'rating_avg' => $l->rating_avg,
                'rating_count' => $l->rating_count,
                'category' => $l->category->name,
                'photo' => $l->photos->first() ? $this->url($l->photos->first()->path) : null,
                'earnedBadges' => $l->badges->where('class', 'earned')->map(fn ($lb) => [
                    'name' => $lb->badge->name, 'icon' => $lb->badge->icon, 'color' => $lb->badge->color, 'label' => $lb->badge->label,
                ])->values(),
                'promotedBadges' => $l->badges->where('class', 'paid')->map(fn ($lb) => [
                    'name' => $lb->badge->name, 'icon' => $lb->badge->icon, 'color' => $lb->badge->color, 'label' => $lb->badge->label,
                ])->values(),
            ]);

        return Inertia::render('Wishlist/Index', ['listings' => $listings]);
    }

    public function toggle(Request $request, Listing $listing)
    {
        $existing = Wishlist::where('user_id', $request->user()->id)->where('listing_id', $listing->id)->first();

        if ($existing) {
            $existing->delete();
            $saved = false;
        } else {
            Wishlist::create(['user_id' => $request->user()->id, 'listing_id' => $listing->id]);
            $saved = true;
        }

        if ($request->wantsJson()) {
            return response()->json(['saved' => $saved]);
        }

        return back()->with('success', $saved ? 'Saved to your wishlist.' : 'Removed from your wishlist.');
    }

    private function url(string $path): string
    {
        return str_starts_with($path, 'http') ? $path : \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }
}
