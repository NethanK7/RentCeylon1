<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Support\PlatformFee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    /**
     * Browse / Search (Page 02). Filters: category, price, location, dates,
     * rating, and category-specific TYPED attributes (e.g. vehicle_type,
     * transmission, fuel, seats). Sponsored listings float to the top, clearly
     * labelled and visually distinct from earned badges (Constraint 01).
     */
    public function index(Request $request): Response
    {
        $category = $request->filled('category')
            ? Category::where('slug', $request->string('category'))->first()
            : null;

        $query = Listing::query()
            ->public()
            ->with(['photos', 'category', 'lister:id,name', 'badges.badge'])
            ->withCount('badges');

        // Text search
        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(fn ($sub) => $sub
                ->where('title', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%"));
        }

        // Category (includes descendants)
        if ($category) {
            $ids = collect([$category->id])
                ->merge($category->children()->pluck('id'))
                ->all();
            $query->whereIn('category_id', $ids);
        }

        // Price range (daily rate)
        if ($request->filled('min_price')) $query->where('daily_rate', '>=', $request->float('min_price'));
        if ($request->filled('max_price')) $query->where('daily_rate', '<=', $request->float('max_price'));

        // Location
        if ($request->filled('city')) $query->where('city', $request->string('city'));
        if ($request->filled('district')) $query->where('district', $request->string('district'));

        // Minimum rating
        if ($request->filled('min_rating')) $query->where('rating_avg', '>=', $request->float('min_rating'));

        // Availability for a date range
        if ($request->filled('start') && $request->filled('end')) {
            $start = $request->date('start');
            $end = $request->date('end');
            $query->whereDoesntHave('unavailabilities', fn ($u) => $u
                ->where('start_date', '<=', $end)
                ->where('end_date', '>=', $start));
        }

        // Typed attribute filters: ?attrs[vehicle_type]=Car&attrs[transmission]=Automatic
        $attrs = $request->input('attrs', []);
        if (is_array($attrs)) {
            foreach (array_filter($attrs) as $key => $value) {
                $query->whereHas('attributeValues', fn ($av) => $av
                    ->whereHas('attribute', fn ($a) => $a->where('key', $key))
                    ->where('value', $value));
            }
        }

        // Sort — sponsored first, then chosen order.
        $sort = $request->string('sort', 'recommended')->toString();
        $query->orderByRaw('(select count(*) from listing_badges lb where lb.listing_id = listings.id and lb.class = ? ) desc', ['paid']);
        match ($sort) {
            'price_low' => $query->orderBy('daily_rate'),
            'price_high' => $query->orderByDesc('daily_rate'),
            'rating' => $query->orderByDesc('rating_avg'),
            'newest' => $query->orderByDesc('published_at'),
            default => $query->orderByDesc('rating_avg')->orderByDesc('bookings_count'),
        };

        $listings = $query->paginate(18)->withQueryString()
            ->through(fn (Listing $l) => $this->cardData($l));

        // Filter metadata for the active category (typed attributes).
        $attributeFilters = $category
            ? $category->resolvedAttributes()
                ->where('is_filterable', true)
                ->map(fn ($a) => [
                    'key' => $a->key, 'label' => $a->label, 'type' => $a->type,
                    'options' => $a->options, 'unit' => $a->unit,
                ])->values()
            : collect();

        return Inertia::render('Listings/Browse', [
            'listings' => $listings,
            'filters' => $request->only(['q', 'category', 'min_price', 'max_price', 'city', 'district', 'min_rating', 'sort', 'start', 'end', 'attrs']),
            'activeCategory' => $category ? ['name' => $category->name, 'slug' => $category->slug, 'kind' => $category->kind] : null,
            'attributeFilters' => $attributeFilters,
            'cities' => $this->cities(),
        ]);
    }

    /**
     * Listing Detail (Page 03). Shows tiered-fee breakdown pre-checkout, deposit
     * explicitly, and earned vs promoted badges in separate zones.
     */
    public function show(Request $request, Listing $listing): Response
    {
        abort_unless($listing->status->value === 'active', 404);

        $listing->increment('views');
        $listing->load([
            'photos', 'category',
            'lister:id,name,city,rating_avg,rating_count,avg_response_minutes,created_at',
            'lister.badges.badge',
            'badges.badge',
            'attributeValues.attribute',
            // NOTE: reviews is a hasManyThrough(bookings) — both tables have created_at,
            // so the order column must be qualified or SQLite throws "ambiguous column".
            'reviews' => fn ($q) => $q->visible()->where('direction', 'renter_to_lister')->orderByDesc('reviews.created_at')->limit(10),
            'reviews.author:id,name,avatar_path',
        ]);

        // Example fee preview for a single day (real calc happens at checkout).
        $feePreview = PlatformFee::breakdown($listing->daily_rate, 1, $listing->security_deposit);

        return Inertia::render('Listings/Show', [
            'listing' => [
                'id' => $listing->id,
                'title' => $listing->title,
                'slug' => $listing->slug,
                'description' => $listing->description,
                'condition' => $listing->condition,
                'daily_rate' => $listing->daily_rate,
                'security_deposit' => $listing->security_deposit,
                'currency' => $listing->currency,
                'city' => $listing->city,
                'district' => $listing->district,
                'lat' => $listing->lat,
                'lng' => $listing->lng,
                'rating_avg' => $listing->rating_avg,
                'rating_count' => $listing->rating_count,
                'views' => $listing->views,
                'photos' => $listing->photos->map(fn ($p) => ['url' => $this->photoUrl($p->path)]),
                'category' => ['name' => $listing->category->name, 'slug' => $listing->category->slug, 'kind' => $listing->category->kind],
                'attributes' => $listing->attributeValues->map(fn ($v) => [
                    'label' => $v->attribute->label,
                    'value' => $v->value,
                    'unit' => $v->attribute->unit,
                ]),
                'earnedBadges' => $this->badges($listing->badges, 'earned'),
                'promotedBadges' => $this->badges($listing->badges, 'paid'),
                'lister' => [
                    'id' => $listing->lister->id,
                    'name' => $listing->lister->name,
                    'city' => $listing->lister->city,
                    'rating_avg' => $listing->lister->rating_avg,
                    'rating_count' => $listing->lister->rating_count,
                    'member_since' => $listing->lister->created_at->format('M Y'),
                    'badges' => $this->userBadges($listing->lister->badges),
                ],
                'reviews' => $listing->reviews->map(fn ($r) => [
                    'rating' => $r->rating, 'body' => $r->body,
                    'author' => $r->author->name,
                    'date' => $r->submitted_at?->format('M Y'),
                ]),
            ],
            'feePreview' => $feePreview,
        ]);
    }

    private function cardData(Listing $l): array
    {
        return [
            'id' => $l->id,
            'title' => $l->title,
            'slug' => $l->slug,
            'daily_rate' => $l->daily_rate,
            'security_deposit' => $l->security_deposit,
            'currency' => $l->currency,
            'city' => $l->city,
            'district' => $l->district,
            'rating_avg' => $l->rating_avg,
            'rating_count' => $l->rating_count,
            'category' => $l->category->name,
            'photo' => $l->photos->first() ? $this->photoUrl($l->photos->first()->path) : null,
            'photos' => $l->photos->take(5)->map(fn ($p) => $this->photoUrl($p->path)),
            'earnedBadges' => $this->badges($l->badges, 'earned'),
            'promotedBadges' => $this->badges($l->badges, 'paid'),
        ];
    }

    private function badges($listingBadges, string $class): array
    {
        return $listingBadges->where('class', $class)->map(fn ($lb) => [
            'key' => $lb->badge->key,
            'name' => $lb->badge->name,
            'icon' => $lb->badge->icon,
            'color' => $lb->badge->color,
            'label' => $lb->badge->label,
        ])->values()->all();
    }

    private function userBadges($userBadges): array
    {
        return $userBadges->map(fn ($ub) => [
            'key' => $ub->badge->key,
            'name' => $ub->badge->name,
            'icon' => $ub->badge->icon,
            'color' => $ub->badge->color,
            'label' => $ub->badge->label,
            'class' => $ub->badge->class->value,
        ])->values()->all();
    }

    private function photoUrl(string $path): string
    {
        return str_starts_with($path, 'http') ? $path : asset('storage/'.$path);
    }

    private function cities(): array
    {
        return ['Colombo', 'Kandy', 'Galle', 'Negombo', 'Jaffna', 'Nuwara Eliya', 'Kurunegala', 'Anuradhapura', 'Batticaloa', 'Matara'];
    }
}
