<?php

namespace App\Enums;

/** Deposit escrow states. Release is manual only (Constraint 03). */
enum DepositStatus: string
{
    case Held = 'held';
    case PendingRelease = 'pending_release';
    case ReturnedToRenter = 'returned_to_renter';
    case ReleasedToLister = 'released_to_lister';
    case PartiallyReleased = 'partially_released';
    case Forfeited = 'forfeited';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
