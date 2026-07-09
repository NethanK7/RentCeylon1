<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingAttributeValue extends Model
{
    protected $fillable = ['listing_id', 'category_attribute_id', 'value', 'value_number'];

    protected function casts(): array
    {
        return ['value_number' => 'float'];
    }

    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
    public function attribute(): BelongsTo { return $this->belongsTo(CategoryAttribute::class, 'category_attribute_id'); }
}
