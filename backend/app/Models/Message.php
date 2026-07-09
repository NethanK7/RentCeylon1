<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'thread_id', 'sender_id', 'body',
        'is_flagged', 'flag_reason', 'held_for_review', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_flagged' => 'boolean',
            'held_for_review' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function thread(): BelongsTo { return $this->belongsTo(MessageThread::class, 'thread_id'); }
    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_id'); }
}
