<?php

namespace App\Models;

use App\Enums\ListingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'description', 'condition',
        'daily_rate', 'security_deposit', 'currency', 'city', 'district',
        'lat', 'lng', 'status', 'specs', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ListingStatus::class,
            'specs' => 'array',
            'daily_rate' => 'float',
            'security_deposit' => 'float',
            'rating_avg' => 'float',
            'lat' => 'float',
            'lng' => 'float',
            'published_at' => 'datetime',
        ];
    }

    public function lister(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function photos(): HasMany { return $this->hasMany(ListingPhoto::class)->orderBy('sort_order'); }
    public function attributeValues(): HasMany { return $this->hasMany(ListingAttributeValue::class); }
    public function unavailabilities(): HasMany { return $this->hasMany(ListingUnavailability::class); }
    public function bookings(): HasMany { return $this->hasMany(Booking::class); }
    public function reviews() { return $this->hasManyThrough(Review::class, Booking::class); }
    public function badges(): HasMany { return $this->hasMany(ListingBadge::class); }

    // Only ID-verified listers' active listings are publicly visible (Constraint 11).
    public function scopePublic($q)
    {
        return $q->where('status', ListingStatus::Active->value)
            ->whereHas('lister', fn ($u) => $u->where('id_verification_status', 'approved')
                ->whereNull('suspended_at'));
    }

    public function isAvailableBetween(string $start, string $end): bool
    {
        return ! $this->unavailabilities()
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->exists();
    }
}
