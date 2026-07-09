<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConditionPhoto extends Model
{
    protected $fillable = [
        'booking_id', 'uploaded_by', 'phase', 'path',
        'taken_at', 'lat', 'lng', 'upload_status',
    ];

    protected function casts(): array
    {
        return ['taken_at' => 'datetime', 'lat' => 'float', 'lng' => 'float'];
    }

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
}
