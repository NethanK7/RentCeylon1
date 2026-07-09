<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingBadge extends Model
{
    protected $fillable = ['listing_id', 'badge_id', 'class', 'expires_at', 'payment_id'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
    public function badge(): BelongsTo { return $this->belongsTo(Badge::class); }
}
