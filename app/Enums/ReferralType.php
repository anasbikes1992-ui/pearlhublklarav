<?php

declare(strict_types=1);

namespace App\Enums;

enum ReferralType: string
{
    case SIGNUP = 'signup';
    case BOOKING = 'booking';
    case LISTING = 'listing';
    case VERIFIED = 'verified';

    public function label(): string
    {
        return match ($this) {
            self::SIGNUP => 'Signup',
            self::BOOKING => 'Booking',
            self::LISTING => 'Listing',
            self::VERIFIED => 'Verified',
        };
    }

    public function points(): int
    {
        return match ($this) {
            self::SIGNUP => 50,
            self::BOOKING => 100,
            self::LISTING => 75,
            self::VERIFIED => 25,
        };
    }
}
