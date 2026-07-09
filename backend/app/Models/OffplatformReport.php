<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffplatformReport extends Model
{
    protected $fillable = [
        'reported_user_id', 'reported_by', 'message_id', 'detail', 'status',
    ];
}
