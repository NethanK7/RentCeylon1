<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceLog extends Model
{
    protected $fillable = [
        'property_id', 'raised_by', 'title', 'description', 'cost', 'status', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['cost' => 'float', 'resolved_at' => 'datetime'];
    }

    public function property(): BelongsTo { return $this->belongsTo(ManagedProperty::class, 'property_id'); }
}
