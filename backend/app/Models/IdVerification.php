<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdVerification extends Model
{
    protected $fillable = [
        'user_id', 'doc_type', 'nic_front_path', 'nic_back_path',
        'passport_path', 'selfie_path', 'status', 'reject_reason',
        'reviewed_by', 'reviewed_at', 'sla_deadline', 'sla_alerted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => VerificationStatus::class,
            'reviewed_at' => 'datetime',
            'sla_deadline' => 'datetime',
            'sla_alerted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
