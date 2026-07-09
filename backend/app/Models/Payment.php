<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'gateway', 'type', 'amount', 'currency', 'status',
        'gateway_reference', 'payment_token', 'idempotency_key', 'gateway_payload', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'gateway_payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
}
