<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\Request;

/** Read APIs shared by the Flutter app. */
class CatalogController extends Controller
{
    public function categories()
    {
        return Category::enabled()->topLevel()->orderBy('sort_order')
            ->with(['children' => fn ($q) => $q->enabled(), 'categoryAttributes'])
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id, 'name' => $c->name, 'slug' => $c->slug,
                'icon' => $c->icon, 'kind' => $c->kind,
                'attributes' => $c->resolvedAttributes()->map(fn ($a) => [
                    'key' => $a->key, 'label' => $a->label, 'type' => $a->type,
                    'options' => $a->options, 'unit' => $a->unit,
                ])->values(),
                'children' => $c->children->map(fn ($ch) => [
                    'name' => $ch->name, 'slug' => $ch->slug, 'icon' => $ch->icon,
                ]),
            ]);
    }

    public function listings(Request $request)
    {
        $query = Listing::public()->with(['photos', 'category', 'badges.badge']);

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where('title', 'like', "%{$q}%");
        }
        if ($request->filled('category')) {
            $cat = Category::where('slug', $request->string('category'))->first();
            if ($cat) {
                $ids = collect([$cat->id])->merge($cat->children()->pluck('id'))->all();
                $query->whereIn('category_id', $ids);
            }
        }
        if ($request->filled('min_price')) $query->where('daily_rate', '>=', $request->float('min_price'));
        if ($request->filled('max_price')) $query->where('daily_rate', '<=', $request->float('max_price'));
        if ($request->filled('city')) $query->where('city', $request->string('city'));

        $attrs = $request->input('attrs', []);
        if (is_array($attrs)) {
            foreach (array_filter($attrs) as $key => $value) {
                $query->whereHas('attributeValues', fn ($av) => $av
                    ->whereHas('attribute', fn ($a) => $a->where('key', $key))
                    ->where('value', $value));
            }
        }

        return $query->orderByDesc('rating_avg')->paginate(20)
            ->through(fn (Listing $l) => $this->card($l));
    }

    public function listing(Listing $listing)
    {
        abort_unless($listing->status->value === 'active', 404);
        $listing->load(['photos', 'category', 'lister:id,name,rating_avg,rating_count', 'attributeValues.attribute', 'badges.badge']);

        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'slug' => $listing->slug,
            'description' => $listing->description,
            'daily_rate' => $listing->daily_rate,
            'security_deposit' => $listing->security_deposit,
            'currency' => $listing->currency,
            'city' => $listing->city,
            'district' => $listing->district,
            'rating_avg' => $listing->rating_avg,
            'rating_count' => $listing->rating_count,
            'photos' => $listing->photos->map(fn ($p) => $this->url($p->path)),
            'category' => $listing->category->name,
            'attributes' => $listing->attributeValues->map(fn ($v) => [
                'label' => $v->attribute->label, 'value' => $v->value, 'unit' => $v->attribute->unit,
            ]),
            'lister' => ['name' => $listing->lister->name, 'rating_avg' => $listing->lister->rating_avg],
            'earned_badges' => $listing->badges->where('class', 'earned')->pluck('badge.name')->values(),
            'promoted_badges' => $listing->badges->where('class', 'paid')->pluck('badge.name')->values(),
        ];
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
        return str_starts_with($path, 'http') ? $path : asset('storage/'.$path);
    }
}
