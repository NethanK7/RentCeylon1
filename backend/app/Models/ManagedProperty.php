<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManagedProperty extends Model
{
    protected $fillable = [
        'owner_id', 'manager_id', 'title', 'address', 'city', 'district',
        'monthly_rent', 'management_fee_rate', 'currency', 'owner_timezone',
        'tenant_name', 'tenant_phone', 'lease_start', 'lease_end', 'status',
    ];

    protected function casts(): array
    {
        return [
            'monthly_rent' => 'float',
            'management_fee_rate' => 'float',
            'lease_start' => 'date',
            'lease_end' => 'date',
        ];
    }

    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }
    public function manager(): BelongsTo { return $this->belongsTo(User::class, 'manager_id'); }
    public function inspections(): HasMany { return $this->hasMany(Inspection::class, 'property_id'); }
    public function maintenanceLogs(): HasMany { return $this->hasMany(MaintenanceLog::class, 'property_id'); }
    public function rentCollections(): HasMany { return $this->hasMany(RentCollection::class, 'property_id'); }
    public function statements(): HasMany { return $this->hasMany(OwnerStatement::class, 'property_id'); }
}
