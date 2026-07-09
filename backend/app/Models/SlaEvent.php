<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaEvent extends Model
{
    protected $fillable = [
        'kind', 'subject_type', 'subject_id', 'due_at', 'alert_at',
        'alerted', 'breached', 'resolved', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'alert_at' => 'datetime',
            'alerted' => 'boolean',
            'breached' => 'boolean',
            'resolved' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }
}
