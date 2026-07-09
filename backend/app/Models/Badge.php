<?php

namespace App\Models;

use App\Enums\BadgeClass;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = ['key', 'name', 'class', 'icon', 'color', 'label', 'criteria'];

    protected function casts(): array
    {
        return ['class' => BadgeClass::class];
    }
}
