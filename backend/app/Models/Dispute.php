<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispute extends Model
{
    protected $fillable = [
        'booking_id', 'raised_by', 'type', 'description', 'status',
        'resolution', 'resolution_to_renter', 'resolution_to_lister', 'resolution_note',
        'resolved_by', 'resolved_at', 'sla_deadline', 'sla_alerted_at',
        'sla_breached', 'appeal_count',
    ];

    protected function casts(): array
    {
        return [
            'resolution_to_renter' => 'float',
            'resolution_to_lister' => 'float',
            'resolved_at' => 'datetime',
            'sla_deadline' => 'datetime',
            'sla_alerted_at' => 'datetime',
            'sla_breached' => 'boolean',
        ];
    }

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function raisedBy(): BelongsTo { return $this->belongsTo(User::class, 'raised_by'); }
    public function resolver(): BelongsTo { return $this->belongsTo(User::class, 'resolved_by'); }
    public function evidence(): HasMany { return $this->hasMany(DisputeEvidence::class); }
}
