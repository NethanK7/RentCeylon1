<?php

namespace App\Enums;

/**
 * Booking state machine (Pages 09–11, 16).
 *
 *  pending_confirmation ─(lister accepts)→ confirmed ─(paid)→ active
 *      → awaiting_return ─(return photos + confirm)→ returned
 *      → completed ─(deposit resolved)→ closed
 *  branches: cancelled, no_show, disputed
 */
enum BookingStatus: string
{
    case PendingConfirmation = 'pending_confirmation';
    case Confirmed = 'confirmed';
    case Active = 'active';
    case AwaitingReturn = 'awaiting_return';
    case Returned = 'returned';
    case Completed = 'completed';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';
    case Disputed = 'disputed';

    /** Allowed forward transitions. */
    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedNext(), true);
    }

    /** @return self[] */
    public function allowedNext(): array
    {
        return match ($this) {
            self::PendingConfirmation => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Active, self::Cancelled, self::NoShow],
            self::Active => [self::AwaitingReturn, self::Disputed],
            self::AwaitingReturn => [self::Returned, self::Disputed],
            self::Returned => [self::Completed, self::Disputed],
            self::Completed => [self::Closed, self::Disputed],
            self::Disputed => [self::Completed, self::Closed],
            self::Closed, self::Cancelled, self::NoShow => [],
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Closed, self::Cancelled, self::NoShow], true);
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
