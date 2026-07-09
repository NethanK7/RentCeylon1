<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeEvidence extends Model
{
    protected $table = 'dispute_evidence';

    protected $fillable = ['dispute_id', 'uploaded_by', 'kind', 'path', 'meta'];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function dispute(): BelongsTo { return $this->belongsTo(Dispute::class); }
}
