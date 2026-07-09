<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmEnquiry extends Model
{
    protected $table = 'pm_enquiries';

    protected $fillable = [
        'user_id', 'name', 'email', 'phone', 'country',
        'property_city', 'message', 'status',
    ];
}
