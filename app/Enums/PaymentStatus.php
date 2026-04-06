<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case DISPUTED = 'disputed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::PAID => 'Paid',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
            self::DISPUTED => 'Disputed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::PAID => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'gray',
            self::PARTIALLY_REFUNDED => 'gray',
            self::DISPUTED => 'danger',
            self::CANCELLED => 'danger',
        };
    }

    public function isSuccessful(): bool
    {
        return match ($this) {
            self::PAID, self::PARTIALLY_REFUNDED => true,
            default => false,
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::PAID, self::FAILED, self::REFUNDED, self::CANCELLED => true,
            default => false,
        };
    }

    public function allowsRefund(): bool
    {
        return match ($this) {
            self::PAID, self::PARTIALLY_REFUNDED => true,
            default => false,
        };
    }
}
