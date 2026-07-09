<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareLink extends Model
{
    protected $fillable = [
        'code', 'target_type', 'listing_id', 'owner_id', 'clicks', 'conversions',
    ];

    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }
}
