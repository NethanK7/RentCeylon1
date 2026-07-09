<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cancellation extends Model
{
    protected $fillable = [
        'booking_id', 'cancelled_by', 'tier',
        'rental_refund', 'deposit_refund', 'lister_compensation', 'reason',
    ];

    protected function casts(): array
    {
        return [
            'rental_refund' => 'float',
            'deposit_refund' => 'float',
            'lister_compensation' => 'float',
        ];
    }

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function cancelledBy(): BelongsTo { return $this->belongsTo(User::class, 'cancelled_by'); }
}
