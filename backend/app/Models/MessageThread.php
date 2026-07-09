<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageThread extends Model
{
    protected $fillable = [
        'listing_id', 'booking_id', 'renter_id', 'lister_id',
        'request_status', 'initiated_by', 'last_message_at',
    ];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    public function messages(): HasMany { return $this->hasMany(Message::class, 'thread_id'); }
    public function renter(): BelongsTo { return $this->belongsTo(User::class, 'renter_id'); }
    public function lister(): BelongsTo { return $this->belongsTo(User::class, 'lister_id'); }
    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
}
