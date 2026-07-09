<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentCollection extends Model
{
    protected $fillable = [
        'property_id', 'period', 'amount', 'management_fee',
        'status', 'due_date', 'paid_at', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'management_fee' => 'float',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo { return $this->belongsTo(ManagedProperty::class, 'property_id'); }
}
