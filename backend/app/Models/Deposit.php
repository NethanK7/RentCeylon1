<?php

namespace App\Models;

use App\Enums\DepositStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    protected $fillable = [
        'booking_id', 'amount', 'currency', 'status',
        'amount_to_renter', 'amount_to_lister',
        'sla_deadline', 'sla_alerted_at',
        'release_channel', 'released_by', 'released_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DepositStatus::class,
            'amount' => 'float',
            'amount_to_renter' => 'float',
            'amount_to_lister' => 'float',
            'sla_deadline' => 'datetime',
            'sla_alerted_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function releasedBy(): BelongsTo { return $this->belongsTo(User::class, 'released_by'); }
}
