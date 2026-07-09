<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $fillable = [
        'booking_id', 'author_id', 'subject_id', 'direction',
        'rating', 'body', 'is_visible', 'submitted_at', 'is_flagged',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'is_flagged' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(User::class, 'subject_id'); }
    public function flags(): HasMany { return $this->hasMany(ReviewFlag::class); }

    public function scopeVisible($q) { return $q->where('is_visible', true); }
}
