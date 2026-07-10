<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Mobile API mirror of the web wishlist (Airbnb-style save/heart). */
class WishlistController extends Controller
{
    public function index(Request $request)
    {
        return Listing::public()
            ->whereHas('wishlistedBy', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with(['photos', 'category', 'badges.badge'])
            ->get()
            ->map(fn (Listing $l) => $this->card($l));
    }

    public function toggle(Request $request, Listing $listing)
    {
        $existing = Wishlist::where('user_id', $request->user()->id)->where('listing_id', $listing->id)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['saved' => false]);
        }

        Wishlist::create(['user_id' => $request->user()->id, 'listing_id' => $listing->id]);
        return response()->json(['saved' => true]);
    }

    private function card(Listing $l): array
    {
        return [
            'id' => $l->id, 'title' => $l->title, 'slug' => $l->slug,
            'daily_rate' => $l->daily_rate, 'currency' => $l->currency,
            'city' => $l->city, 'rating_avg' => $l->rating_avg, 'rating_count' => $l->rating_count,
            'category' => $l->category->name,
            'photo' => $l->photos->first() ? $this->url($l->photos->first()->path) : null,
            'earned_badges' => $l->badges->where('class', 'earned')->pluck('badge.name')->values(),
            'promoted_badges' => $l->badges->where('class', 'paid')->pluck('badge.name')->values(),
        ];
    }

    private function url(string $path): string
    {
        return str_starts_with($path, 'http') ? $path : Storage::disk('public')->url($path);
    }
}
