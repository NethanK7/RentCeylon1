<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewFlag extends Model
{
    protected $fillable = [
        'review_id', 'flagged_by', 'reason', 'detail',
        'status', 'moderated_by', 'moderated_at',
    ];

    protected function casts(): array
    {
        return ['moderated_at' => 'datetime'];
    }

    public function review(): BelongsTo { return $this->belongsTo(Review::class); }
}
