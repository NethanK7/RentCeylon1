<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionPhoto extends Model
{
    protected $fillable = ['inspection_id', 'path', 'taken_at', 'lat', 'lng'];

    protected function casts(): array
    {
        return ['taken_at' => 'datetime', 'lat' => 'float', 'lng' => 'float'];
    }

    public function inspection(): BelongsTo { return $this->belongsTo(Inspection::class); }
}
