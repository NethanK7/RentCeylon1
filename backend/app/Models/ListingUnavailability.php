<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingUnavailability extends Model
{
    protected $fillable = ['listing_id', 'start_date', 'end_date', 'reason', 'booking_id'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
}
