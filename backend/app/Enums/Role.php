<?php

namespace App\Enums;

enum Role: string
{
    case Renter = 'renter';
    case Lister = 'lister';
    case Admin = 'admin';
    case Manager = 'manager';   // Property Management local manager

    public function label(): string
    {
        return match ($this) {
            self::Renter => 'Renter',
            self::Lister => 'Lister',
            self::Admin => 'Admin',
            self::Manager => 'Property Manager',
        };
    }
}
