<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\DepositStatus;
use App\Models\Booking;
use App\Models\Cancellation;
use App\Models\Deposit;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\RentalAgreement;
use App\Models\User;
use App\Support\PlatformFee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Encapsulates the booking/checkout rules:
 *  - tiered fee snapshot (Amendment 08)
 *  - deposit collected at checkout, held in escrow (Constraints 03, Page 09)
 *  - idempotent payment (Constraint 06 — double-fire = one charge)
 *  - policy + rental-agreement acceptance captured pre-payment
 *  - availability guard (no overlapping bookings)
 */
class BookingService
{
    public function quote(Listing $listing, string $start, string $end): array
    {
        $days = $this->days($start, $end);
        return PlatformFee::breakdown($listing->daily_rate, $days, $listing->security_deposit)
            + ['start' => $start, 'end' => $end];
    }

    public function days(string $start, string $end): int
    {
        $s = Carbon::parse($start)->startOfDay();
        $e = Carbon::parse($end)->startOfDay();
        return max(1, $s->diffInDays($e) + 1);
    }

    /**
     * Create a booking + capture payment idempotently.
     * If the same idempotency key was already used, returns the existing booking
     * (Constraint 06). Everything runs in one transaction.
     */
    public function checkout(
        Listing $listing,
        User $renter,
        string $start,
        string $end,
        string $gateway,
        string $idempotencyKey,
        bool $acceptedPolicy,
        bool $acceptedAgreement,
    ): Booking {
        // Idempotency: if this key already produced a payment, return its booking.
        $existing = Payment::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing->booking;
        }

        if (! $acceptedPolicy || ! $acceptedAgreement) {
            throw ValidationException::withMessages([
                'agreement' => 'Cancellation policy and rental agreement must be accepted before payment.',
            ]);
        }

        if ($renter->id === $listing->user_id) {
            throw ValidationException::withMessages(['listing' => 'You cannot book your own listing.']);
        }

        if (! $listing->isAvailableBetween($start, $end)) {
            throw ValidationException::withMessages(['dates' => 'These dates are no longer available.']);
        }

        $days = $this->days($start, $end);
        $b = PlatformFee::breakdown($listing->daily_rate, $days, $listing->security_deposit);

        return DB::transaction(function () use ($listing, $renter, $start, $end, $days, $b, $gateway, $idempotencyKey) {
            $booking = Booking::create([
                'reference' => 'RC-'.strtoupper(Str::random(6)),
                'listing_id' => $listing->id,
                'renter_id' => $renter->id,
                'lister_id' => $listing->user_id,
                'start_date' => $start,
                'end_date' => $end,
                'days' => $days,
                'daily_rate' => $listing->daily_rate,
                'subtotal' => $b['subtotal'],
                'fee_rate' => $b['fee_rate'],
                'platform_fee' => $b['platform_fee'],
                'deposit_amount' => $b['deposit'],
                'total' => $b['total'],
                'currency' => $listing->currency,
                'status' => BookingStatus::PendingConfirmation,
                'cancellation_policy_accepted_at' => now(),
                'rental_agreement_accepted_at' => now(),
            ]);

            // Rental payment (idempotent) — tokenised, no raw card data (Constraint 09).
            Payment::create([
                'booking_id' => $booking->id,
                'gateway' => $gateway,
                'type' => 'rental',
                'amount' => $b['subtotal'] + $b['platform_fee'],
                'currency' => $listing->currency,
                'status' => 'succeeded', // simulated capture; real gateway callback in prod
                'gateway_reference' => 'SIM-'.Str::upper(Str::random(10)),
                'payment_token' => 'tok_'.Str::random(24),
                'idempotency_key' => $idempotencyKey,
                'processed_at' => now(),
            ]);

            // Deposit payment + escrow ledger (held — Constraint 03).
            Payment::create([
                'booking_id' => $booking->id,
                'gateway' => $gateway,
                'type' => 'deposit',
                'amount' => $b['deposit'],
                'currency' => $listing->currency,
                'status' => 'succeeded',
                'gateway_reference' => 'SIM-'.Str::upper(Str::random(10)),
                'payment_token' => 'tok_'.Str::random(24),
                'idempotency_key' => $idempotencyKey.'-deposit',
                'processed_at' => now(),
            ]);

            Deposit::create([
                'booking_id' => $booking->id,
                'amount' => $b['deposit'],
                'currency' => $listing->currency,
                'status' => DepositStatus::Held,
            ]);

            // Payment confirmed → reveal contact + mark paid (Constraint 04).
            $booking->update([
                'status' => BookingStatus::Confirmed,
                'phone_revealed' => true,
                'confirmed_at' => now(),
                'paid_at' => now(),
            ]);

            // Block the dates.
            $listing->unavailabilities()->create([
                'start_date' => $start, 'end_date' => $end,
                'reason' => 'booked', 'booking_id' => $booking->id,
            ]);

            // Snapshot rental agreement (PDF generated by a job in prod).
            RentalAgreement::create([
                'booking_id' => $booking->id,
                'snapshot' => [
                    'listing' => $listing->title,
                    'renter' => $renter->name,
                    'lister_id' => $listing->user_id,
                    'dates' => [$start, $end],
                    'total' => $b['total'],
                    'deposit' => $b['deposit'],
                    'terms_version' => 'v2.0',
                ],
                'generated_at' => now(),
            ]);

            return $booking;
        });
    }

    /**
     * Pre-pickup cancellation with tiered refund logic (Page 23):
     *   7+ days before  → full rental refund minus platform fee, deposit returned
     *   3–6 days before → 50% rental refund, deposit returned
     *   <3 days before  → no rental refund, deposit returned, 25% of rental
     *                     fee auto-paid to the lister as late-cancel compensation
     * Deposit is always returned to the renter unless it's a no-show.
     */
    public function cancel(Booking $booking, User $cancelledBy, ?string $reason = null): Cancellation
    {
        if ($booking->status !== BookingStatus::Confirmed) {
            throw ValidationException::withMessages(['status' => 'This booking can no longer be cancelled online.']);
        }

        $daysUntilStart = Carbon::today()->diffInDays($booking->start_date, false);

        return DB::transaction(function () use ($booking, $cancelledBy, $reason, $daysUntilStart) {
            if ($daysUntilStart >= 7) {
                $tier = 't_7plus';
                $rentalRefund = $booking->subtotal;
                $listerCompensation = 0.0;
            } elseif ($daysUntilStart >= 3) {
                $tier = 't_3_6';
                $rentalRefund = round($booking->subtotal * 0.5, 2);
                $listerCompensation = 0.0;
            } else {
                $tier = 't_under_3';
                $rentalRefund = 0.0;
                $listerCompensation = round($booking->subtotal * 0.25, 2);
            }

            $depositRefund = $booking->deposit_amount;

            $cancellation = Cancellation::create([
                'booking_id' => $booking->id,
                'cancelled_by' => $cancelledBy->id,
                'tier' => $tier,
                'rental_refund' => $rentalRefund,
                'deposit_refund' => $depositRefund,
                'lister_compensation' => $listerCompensation,
                'reason' => $reason,
            ]);

            if ($rentalRefund > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'gateway' => 'payhere',
                    'type' => 'refund',
                    'amount' => $rentalRefund,
                    'currency' => $booking->currency,
                    'status' => 'succeeded',
                    'gateway_reference' => 'SIM-'.Str::upper(Str::random(10)),
                    'idempotency_key' => 'cancel-refund-'.$booking->id,
                    'processed_at' => now(),
                ]);
            }

            if ($listerCompensation > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'gateway' => 'payhere',
                    'type' => 'lister_compensation',
                    'amount' => $listerCompensation,
                    'currency' => $booking->currency,
                    'status' => 'succeeded',
                    'gateway_reference' => 'SIM-'.Str::upper(Str::random(10)),
                    'idempotency_key' => 'cancel-comp-'.$booking->id,
                    'processed_at' => now(),
                ]);
            }

            $booking->deposit?->update([
                'status' => DepositStatus::ReturnedToRenter->value,
                'amount_to_renter' => $depositRefund,
                'release_channel' => 'cancellation',
                'released_by' => $cancelledBy->id,
                'released_at' => now(),
            ]);

            $booking->update(['status' => BookingStatus::Cancelled->value]);

            // Free up the dates.
            $booking->listing->unavailabilities()->where('booking_id', $booking->id)->delete();

            return $cancellation;
        });
    }
}
