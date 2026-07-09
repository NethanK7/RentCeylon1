<?php

namespace App\Http\Controllers\Lister;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingAttributeValue;
use App\Models\ListingPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Lister: Create/Edit Listing (Page 14) + My Listings (Page 15).
 *
 * Enforces: only enabled, "selectable" (leaf) categories; min 3 photos;
 * ID-verified before a listing can go live (Constraint 11) — unverified
 * listers can still draft/save, but the listing stays hidden until they pass
 * verification.
 */
class ListingManageController extends Controller
{
    public function index(Request $request): Response
    {
        $listings = $request->user()->listings()
            ->with(['photos', 'category'])
            ->withCount('bookings')
            ->latest()
            ->get()
            ->map(fn (Listing $l) => [
                'id' => $l->id,
                'title' => $l->title,
                'slug' => $l->slug,
                'status' => $l->status->value,
                'status_label' => $l->status->label(),
                'daily_rate' => $l->daily_rate,
                'currency' => $l->currency,
                'category' => $l->category->name,
                'views' => $l->views,
                'bookings_count' => $l->bookings_count,
                'rating_avg' => $l->rating_avg,
                'rating_count' => $l->rating_count,
                'photo' => $l->photos->first() ? $this->url($l->photos->first()->path) : null,
            ]);

        return Inertia::render('Lister/Listings/Index', [
            'listings' => $listings,
            'isIdVerified' => $request->user()->isIdVerified(),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Lister/Listings/Create', [
            'categoryGroups' => $this->categoryGroups(),
            'isIdVerified' => $request->user()->isIdVerified(),
            'cities' => $this->cities(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateListing($request);
        $category = Category::findOrFail($validated['category_id']);

        $listing = $request->user()->listings()->create([
            'category_id' => $category->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(6)),
            'description' => $validated['description'],
            'condition' => $validated['condition'],
            'daily_rate' => $validated['daily_rate'],
            'security_deposit' => $validated['security_deposit'] ?? 0,
            'currency' => 'LKR',
            'city' => $validated['city'],
            'district' => $validated['district'],
            'status' => $request->user()->isIdVerified() ? ListingStatus::Active->value : ListingStatus::PendingVerification->value,
            'published_at' => $request->user()->isIdVerified() ? now() : null,
        ]);

        $this->syncPhotos($listing, $request);
        $this->syncAttributes($listing, $category, $request->input('attrs', []));

        return redirect()->route('lister.listings.index')->with('success',
            $request->user()->isIdVerified()
                ? 'Listing published!'
                : 'Listing saved. It will go live once your ID verification is approved.'
        );
    }

    public function edit(Request $request, Listing $listing): Response
    {
        $this->authorizeOwner($request, $listing);
        $listing->load('attributeValues.attribute', 'photos');

        return Inertia::render('Lister/Listings/Edit', [
            'listing' => [
                'id' => $listing->id,
                'title' => $listing->title,
                'description' => $listing->description,
                'condition' => $listing->condition,
                'daily_rate' => $listing->daily_rate,
                'security_deposit' => $listing->security_deposit,
                'city' => $listing->city,
                'district' => $listing->district,
                'category_id' => $listing->category_id,
                'photos' => $listing->photos->map(fn ($p) => ['id' => $p->id, 'url' => $this->url($p->path)]),
                'attrs' => $listing->attributeValues->mapWithKeys(fn ($v) => [$v->attribute->key => $v->value]),
            ],
            'categoryGroups' => $this->categoryGroups(),
            'isIdVerified' => $request->user()->isIdVerified(),
            'cities' => $this->cities(),
        ]);
    }

    public function update(Request $request, Listing $listing)
    {
        $this->authorizeOwner($request, $listing);
        $validated = $this->validateListing($request);
        $category = Category::findOrFail($validated['category_id']);

        $listing->update([
            'category_id' => $category->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'condition' => $validated['condition'],
            'daily_rate' => $validated['daily_rate'],
            'security_deposit' => $validated['security_deposit'] ?? 0,
            'city' => $validated['city'],
            'district' => $validated['district'],
        ]);

        $this->syncPhotos($listing, $request, append: true);
        $this->syncAttributes($listing, $category, $request->input('attrs', []));

        return redirect()->route('lister.listings.index')->with('success', 'Listing updated.');
    }

    public function pause(Request $request, Listing $listing)
    {
        $this->authorizeOwner($request, $listing);
        $listing->update(['status' => ListingStatus::Paused->value]);
        return back()->with('success', 'Listing paused. It is hidden from renters until reactivated.');
    }

    public function activate(Request $request, Listing $listing)
    {
        $this->authorizeOwner($request, $listing);
        if (! $request->user()->isIdVerified()) {
            return back()->with('error', 'Verify your ID before reactivating listings.');
        }
        $listing->update(['status' => ListingStatus::Active->value, 'published_at' => $listing->published_at ?? now()]);
        return back()->with('success', 'Listing is live again.');
    }

    public function destroy(Request $request, Listing $listing)
    {
        $this->authorizeOwner($request, $listing);
        if ($listing->bookings()->whereNotIn('status', ['closed', 'cancelled', 'no_show'])->exists()) {
            return back()->with('error', 'Cannot delete a listing with an active booking.');
        }
        $listing->update(['status' => ListingStatus::Removed->value]);
        $listing->delete();
        return back()->with('success', 'Listing removed.');
    }

    private function authorizeOwner(Request $request, Listing $listing): void
    {
        abort_unless($listing->user_id === $request->user()->id, 403);
    }

    private function validateListing(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where('is_enabled', true)],
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:20',
            'condition' => 'required|in:new,like_new,good,fair',
            'daily_rate' => 'required|numeric|min:1',
            'security_deposit' => 'nullable|numeric|min:0',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'photos' => $request->isMethod('post') ? 'required|array|min:3' : 'nullable|array',
            'photos.*' => 'image|max:8192',
        ]);
    }

    private function syncPhotos(Listing $listing, Request $request, bool $append = false): void
    {
        if (! $request->hasFile('photos')) return;

        $startOrder = $append ? $listing->photos()->max('sort_order') + 1 : 0;
        foreach ($request->file('photos') as $i => $file) {
            $path = $file->store("listings/{$listing->id}", 'public');
            ListingPhoto::create([
                'listing_id' => $listing->id,
                'path' => $path,
                'sort_order' => $startOrder + $i,
            ]);
        }
    }

    private function syncAttributes(Listing $listing, Category $category, array $attrs): void
    {
        $defs = $category->resolvedAttributes()->keyBy('key');
        $listing->attributeValues()->delete();

        foreach ($attrs as $key => $value) {
            if ($value === null || $value === '') continue;
            $def = $defs->get($key);
            if (! $def) continue;

            ListingAttributeValue::create([
                'listing_id' => $listing->id,
                'category_attribute_id' => $def->id,
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'value_number' => is_numeric($value) ? (float) $value : null,
            ]);
        }
    }

    /** Selectable categories grouped by parent, with resolved (inherited) attributes per option. */
    private function categoryGroups(): array
    {
        $tops = Category::enabled()->topLevel()->orderBy('sort_order')->with('children')->get();

        return $tops->map(function (Category $top) {
            $options = $top->children->where('is_enabled', true)->isNotEmpty()
                ? $top->children->where('is_enabled', true)->values()
                : collect([$top]);

            return [
                'group' => $top->name,
                'options' => $options->map(fn (Category $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'attributes' => $c->resolvedAttributes()->map(fn ($a) => [
                        'key' => $a->key, 'label' => $a->label, 'type' => $a->type,
                        'options' => $a->options, 'unit' => $a->unit, 'required' => $a->is_required,
                    ])->values(),
                ])->values(),
            ];
        })->values()->all();
    }

    private function cities(): array
    {
        return ['Colombo', 'Kandy', 'Galle', 'Negombo', 'Jaffna', 'Nuwara Eliya', 'Kurunegala', 'Anuradhapura', 'Batticaloa', 'Matara'];
    }

    private function url(string $path): string
    {
        return str_starts_with($path, 'http') ? $path : Storage::disk('public')->url($path);
    }
}
