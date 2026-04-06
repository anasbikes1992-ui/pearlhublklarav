<?php

declare(strict_types=1);

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CHECKED_IN = 'checked_in';
    case CHECKED_OUT = 'checked_out';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case NO_SHOW = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::CHECKED_IN => 'Checked In',
            self::CHECKED_OUT => 'Checked Out',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
            self::NO_SHOW => 'No Show',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'success',
            self::CHECKED_IN => 'info',
            self::CHECKED_OUT => 'info',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
            self::REFUNDED => 'gray',
            self::NO_SHOW => 'danger',
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::COMPLETED, self::CANCELLED, self::REFUNDED, self::NO_SHOW => true,
            default => false,
        };
    }

    public function canCancel(): bool
    {
        return match ($this) {
            self::PENDING, self::CONFIRMED => true,
            default => false,
        };
    }

    public function canRefund(): bool
    {
        return match ($this) {
            self::CANCELLED, self::NO_SHOW => true,
            default => false,
        };
    }
}
