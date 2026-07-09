<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Unsubmitted = 'unsubmitted';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
