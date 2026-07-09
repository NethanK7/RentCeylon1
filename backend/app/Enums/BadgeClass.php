<?php

namespace App\Enums;

/**
 * Global Constraint 01: paid and earned badges must NEVER be mixed —
 * different shape, colour family, and label on every one of the 28 pages.
 */
enum BadgeClass: string
{
    case Earned = 'earned';
    case Paid = 'paid';
}
