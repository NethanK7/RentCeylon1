<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Draft = 'draft';
    case PendingVerification = 'pending_verification';
    case Active = 'active';
    case Paused = 'paused';
    case Removed = 'removed';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
