<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case PROVIDER = 'provider';
    case CUSTOMER = 'customer';
    case DRIVER = 'driver';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::PROVIDER => 'Provider',
            self::CUSTOMER => 'Customer',
            self::DRIVER => 'Driver',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'danger',
            self::PROVIDER => 'warning',
            self::CUSTOMER => 'success',
            self::DRIVER => 'info',
        };
    }

    public function canAccessAdmin(): bool
    {
        return match ($this) {
            self::ADMIN => true,
            self::PROVIDER, self::CUSTOMER, self::DRIVER => false,
        };
    }

    public function canList(): bool
    {
        return match ($this) {
            self::ADMIN, self::PROVIDER => true,
            self::CUSTOMER, self::DRIVER => false,
        };
    }

    public function canBook(): bool
    {
        return match ($this) {
            self::ADMIN, self::CUSTOMER => true,
            self::PROVIDER, self::DRIVER => false,
        };
    }
}
