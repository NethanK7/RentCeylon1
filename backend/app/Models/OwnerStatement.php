<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerStatement extends Model
{
    protected $fillable = ['property_id', 'period', 'pdf_path', 'summary', 'generated_at'];

    protected function casts(): array
    {
        return ['summary' => 'array', 'generated_at' => 'datetime'];
    }

    public function property(): BelongsTo { return $this->belongsTo(ManagedProperty::class, 'property_id'); }
}
