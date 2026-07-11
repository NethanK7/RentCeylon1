<?php

namespace App\Models;

use App\Enums\Role;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'avatar_path', 'avatar_url',
        'city', 'district', 'bio', 'tos_accepted_at', 'id_verification_status',
        'referral_code', 'referred_by', 'google_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'tos_accepted_at' => 'datetime',
            'suspended_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'id_verification_status' => VerificationStatus::class,
            'rating_avg' => 'float',
        ];
    }

    // ── Role helpers ──
    public function isAdmin(): bool { return $this->role === Role::Admin; }
    public function isLister(): bool { return $this->role === Role::Lister; }
    public function isRenter(): bool { return $this->role === Role::Renter; }
    public function isManager(): bool { return $this->role === Role::Manager; }

    public function isIdVerified(): bool
    {
        return $this->id_verification_status === VerificationStatus::Approved;
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    // ── Relationships ──
    public function listings(): HasMany { return $this->hasMany(Listing::class); }

    public function bookingsAsRenter(): HasMany { return $this->hasMany(Booking::class, 'renter_id'); }
    public function bookingsAsLister(): HasMany { return $this->hasMany(Booking::class, 'lister_id'); }

    public function idVerification(): HasOne
    {
        return $this->hasOne(IdVerification::class)->latestOfMany();
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function badges(): HasMany { return $this->hasMany(UserBadge::class); }
    public function referralsMade(): HasMany { return $this->hasMany(Referral::class, 'referrer_id'); }
    public function managedProperties(): HasMany { return $this->hasMany(ManagedProperty::class, 'owner_id'); }
    public function assignedProperties(): HasMany { return $this->hasMany(ManagedProperty::class, 'manager_id'); }
    public function wishlist(): HasMany { return $this->hasMany(Wishlist::class); }
}
