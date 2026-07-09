<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'slug', 'icon', 'description',
        'kind', 'is_enabled', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }

    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order'); }
    // NOTE: not named attributes() — that collides with Eloquent's attribute bag.
    public function categoryAttributes(): HasMany { return $this->hasMany(CategoryAttribute::class)->orderBy('sort_order'); }
    public function listings(): HasMany { return $this->hasMany(Listing::class); }

    public function scopeEnabled($q) { return $q->where('is_enabled', true); }
    public function scopeTopLevel($q) { return $q->whereNull('parent_id'); }

    /**
     * Attributes usable on this category = its own + all ancestor attributes.
     * Lets a child like "Cars" inherit the "Vehicles" filter set (vehicle_type,
     * transmission, fuel, seats…) without redefining them per child.
     *
     * @return \Illuminate\Support\Collection<int,\App\Models\CategoryAttribute>
     */
    public function resolvedAttributes()
    {
        $collected = collect();
        $node = $this->load('categoryAttributes', 'parent.categoryAttributes', 'parent.parent.categoryAttributes');

        while ($node) {
            $collected = $collected->merge($node->categoryAttributes);
            $node = $node->parent;
        }

        // De-dupe by key (child overrides ancestor), keep filterable ordering.
        return $collected->unique('key')->sortBy('sort_order')->values();
    }
}
