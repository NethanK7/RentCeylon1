<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspection extends Model
{
    protected $fillable = [
        'property_id', 'manager_id', 'scheduled_at', 'completed_at', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function property(): BelongsTo { return $this->belongsTo(ManagedProperty::class, 'property_id'); }
    public function photos(): HasMany { return $this->hasMany(InspectionPhoto::class); }
}
