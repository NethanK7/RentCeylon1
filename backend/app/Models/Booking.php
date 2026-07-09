<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'reference', 'listing_id', 'renter_id', 'lister_id',
        'start_date', 'end_date', 'days',
        'daily_rate', 'subtotal', 'fee_rate', 'platform_fee', 'deposit_amount', 'total', 'currency',
        'status', 'phone_revealed',
        'cancellation_policy_accepted_at', 'rental_agreement_accepted_at',
        'confirmed_at', 'paid_at', 'started_at', 'returned_at', 'completed_at', 'closed_at',
        'return_confirm_deadline',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'phone_revealed' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'daily_rate' => 'float',
            'subtotal' => 'float',
            'fee_rate' => 'float',
            'platform_fee' => 'float',
            'deposit_amount' => 'float',
            'total' => 'float',
            'cancellation_policy_accepted_at' => 'datetime',
            'rental_agreement_accepted_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'paid_at' => 'datetime',
            'started_at' => 'datetime',
            'returned_at' => 'datetime',
            'completed_at' => 'datetime',
            'closed_at' => 'datetime',
            'return_confirm_deadline' => 'datetime',
        ];
    }

    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
    public function renter(): BelongsTo { return $this->belongsTo(User::class, 'renter_id'); }
    public function lister(): BelongsTo { return $this->belongsTo(User::class, 'lister_id'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function deposit(): HasOne { return $this->hasOne(Deposit::class); }
    public function conditionPhotos(): HasMany { return $this->hasMany(ConditionPhoto::class); }
    public function cancellation(): HasOne { return $this->hasOne(Cancellation::class); }
    public function rentalAgreement(): HasOne { return $this->hasOne(RentalAgreement::class); }
    public function dispute(): HasOne { return $this->hasOne(Dispute::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }

    /** Condition-photo hard gate helpers (Constraint 02). */
    public function hasPickupPhotos(): bool
    {
        return $this->conditionPhotos()->where('phase', 'pickup')
            ->where('upload_status', 'uploaded')->exists();
    }

    public function hasReturnPhotos(): bool
    {
        return $this->conditionPhotos()->where('phase', 'return')
            ->where('upload_status', 'uploaded')->exists();
    }

    public function isClosed(): bool
    {
        return $this->status === BookingStatus::Closed;
    }
}
