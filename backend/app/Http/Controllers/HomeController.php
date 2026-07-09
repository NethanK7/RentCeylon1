<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /** Homepage (Page 01) — trust-first entry point, Airbnb-style city rows. */
    public function index(): Response
    {
        $featured = Listing::public()
            ->with(['photos', 'category', 'badges.badge'])
            ->orderByRaw('(select count(*) from listing_badges lb where lb.listing_id = listings.id and lb.class = ?) desc', ['paid'])
            ->orderByDesc('rating_avg')
            ->limit(8)->get()
            ->map(fn (Listing $l) => $this->card($l));

        $categories = Category::enabled()->topLevel()->orderBy('sort_order')
            ->withCount(['listings' => fn ($q) => $q->public()])
            ->get(['id', 'name', 'slug', 'icon', 'kind', 'description'])
            ->map(fn ($c) => [
                'name' => $c->name, 'slug' => $c->slug, 'icon' => $c->icon,
                'kind' => $c->kind, 'description' => $c->description,
                'count' => $c->listings_count,
            ]);

        // Airbnb-style horizontal rows: top cities by listing count.
        $topCities = Listing::public()
            ->selectRaw('city, count(*) as c')
            ->groupBy('city')->orderByDesc('c')->limit(4)
            ->pluck('city');

        $cityRows = $topCities->map(fn ($city) => [
            'title' => "Popular rentals in {$city}",
            'listings' => Listing::public()
                ->where('city', $city)
                ->with(['photos', 'category', 'badges.badge'])
                ->orderByDesc('rating_avg')->limit(8)->get()
                ->map(fn (Listing $l) => $this->card($l)),
        ])->filter(fn ($row) => $row['listings']->isNotEmpty())->values();

        return Inertia::render('Home', [
            'featured' => $featured,
            'categories' => $categories,
            'cityRows' => $cityRows,
            'stats' => [
                'listings' => Listing::public()->count(),
                'cities' => Listing::public()->distinct('city')->count('city'),
            ],
        ]);
    }

    private function card(Listing $l): array
    {
        $photo = $l->photos->first();

        return [
            'id' => $l->id,
            'title' => $l->title,
            'slug' => $l->slug,
            'daily_rate' => $l->daily_rate,
            'currency' => $l->currency,
            'city' => $l->city,
            'rating_avg' => $l->rating_avg,
            'rating_count' => $l->rating_count,
            'category' => $l->category->name,
            'photo' => $photo ? (str_starts_with($photo->path, 'http') ? $photo->path : asset('storage/'.$photo->path)) : null,
            'earnedBadges' => $l->badges->where('class', 'earned')->map(fn ($lb) => [
                'name' => $lb->badge->name, 'icon' => $lb->badge->icon, 'color' => $lb->badge->color, 'label' => $lb->badge->label,
            ])->values(),
            'promotedBadges' => $l->badges->where('class', 'paid')->map(fn ($lb) => [
                'name' => $lb->badge->name, 'icon' => $lb->badge->icon, 'color' => $lb->badge->color, 'label' => $lb->badge->label,
            ])->values(),
        ];
    }
}
