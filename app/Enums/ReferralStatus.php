<?php

declare(strict_types=1);

namespace App\Enums;

enum ReferralStatus: string
{
    case PENDING = 'pending';
    case QUALIFIED = 'qualified';
    case COMPLETED = 'completed';
    case PAID = 'paid';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::QUALIFIED => 'Qualified',
            self::COMPLETED => 'Completed',
            self::PAID => 'Paid',
            self::EXPIRED => 'Expired',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::QUALIFIED => 'info',
            self::COMPLETED => 'success',
            self::PAID => 'success',
            self::EXPIRED => 'gray',
            self::CANCELLED => 'danger',
        };
    }

    public function isActive(): bool
    {
        return match ($this) {
            self::PENDING, self::QUALIFIED, self::COMPLETED => true,
            default => false,
        };
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }
}
