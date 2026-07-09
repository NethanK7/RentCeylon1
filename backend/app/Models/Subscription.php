<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'tier', 'price', 'currency', 'status',
        'listing_limit', 'photo_slots', 'badge_eligible',
        'current_period_end', 'grace_ends_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'badge_eligible' => 'boolean',
            'current_period_end' => 'datetime',
            'grace_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function invoices(): HasMany { return $this->hasMany(SubscriptionInvoice::class); }
}
