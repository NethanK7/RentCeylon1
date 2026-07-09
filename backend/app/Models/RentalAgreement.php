<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalAgreement extends Model
{
    protected $fillable = ['booking_id', 'pdf_path', 'snapshot', 'generated_at'];

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'generated_at' => 'datetime'];
    }

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
}
