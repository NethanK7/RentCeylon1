<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'subscription_id', 'user_id', 'amount', 'currency',
        'status', 'gateway_reference', 'issued_at',
    ];

    protected function casts(): array
    {
        return ['amount' => 'float', 'issued_at' => 'datetime'];
    }

    public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); }
}
