<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id', 'invited_email', 'referred_user_id',
        'status', 'reward_type', 'converted_at', 'rewarded_at',
    ];

    protected function casts(): array
    {
        return ['converted_at' => 'datetime', 'rewarded_at' => 'datetime'];
    }

    public function referrer(): BelongsTo { return $this->belongsTo(User::class, 'referrer_id'); }
    public function referred(): BelongsTo { return $this->belongsTo(User::class, 'referred_user_id'); }
}
