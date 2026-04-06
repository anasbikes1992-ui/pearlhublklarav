<?php

declare(strict_types=1);

namespace App\Enums;

enum VerticalType: string
{
    case PROPERTY = 'property';
    case STAY = 'stay';
    case VEHICLE = 'vehicle';
    case TAXI = 'taxi';
    case EVENT = 'event';
    case SME = 'sme';
    case EXPERIENCE = 'experience';

    public function label(): string
    {
        return match ($this) {
            self::PROPERTY => 'Property',
            self::STAY => 'Stay',
            self::VEHICLE => 'Vehicle',
            self::TAXI => 'Taxi',
            self::EVENT => 'Event',
            self::SME => 'SME',
            self::EXPERIENCE => 'Experience',
        };
    }

    public function pluralLabel(): string
    {
        return match ($this) {
            self::PROPERTY => 'Properties',
            self::STAY => 'Stays',
            self::VEHICLE => 'Vehicles',
            self::TAXI => 'Taxis',
            self::EVENT => 'Events',
            self::SME => 'SMEs',
            self::EXPERIENCE => 'Experiences',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PROPERTY => 'heroicon-o-home',
            self::STAY => 'heroicon-o-building-office',
            self::VEHICLE => 'heroicon-o-truck',
            self::TAXI => 'heroicon-o-car',
            self::EVENT => 'heroicon-o-calendar',
            self::SME => 'heroicon-o-building-storefront',
            self::EXPERIENCE => 'heroicon-o-sparkles',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PROPERTY => '#3B82F6',
            self::STAY => '#10B981',
            self::VEHICLE => '#8B5CF6',
            self::TAXI => '#F59E0B',
            self::EVENT => '#EF4444',
            self::SME => '#6B7280',
            self::EXPERIENCE => '#EC4899',
        };
    }

    public function flowType(): string
    {
        return match ($this) {
            self::PROPERTY, self::STAY, self::VEHICLE, self::EVENT, self::EXPERIENCE => 'booking',
            self::TAXI => 'booking',
            self::SME => 'inquiry_only',
        };
    }

    public function requiresEscrow(): bool
    {
        return match ($this) {
            self::PROPERTY, self::STAY, self::VEHICLE, self::EVENT, self::EXPERIENCE => true,
            self::TAXI, self::SME => false,
        };
    }

    public function cancellationWindowHours(): int
    {
        return match ($this) {
            self::PROPERTY => 48,
            self::STAY => 24,
            self::VEHICLE => 24,
            self::TAXI => 1,
            self::EVENT => 72,
            self::SME => 0,
            self::EXPERIENCE => 48,
        };
    }

    public function commissionRate(): float
    {
        return match ($this) {
            self::PROPERTY => 0.06,
            self::STAY => 0.09,
            self::VEHICLE => 0.08,
            self::TAXI => 0.12,
            self::EVENT => 0.10,
            self::SME => 0.00,
            self::EXPERIENCE => 0.15,
        };
    }

    public function taxRate(): float
    {
        return 0.18;
    }

    public function productLimit(): ?int
    {
        return match ($this) {
            self::SME => 100,
            default => null,
        };
    }

    public function bufferHours(): int
    {
        return match ($this) {
            self::STAY => 2,
            self::VEHICLE => 1,
            self::EXPERIENCE => 4,
            default => 0,
        };
    }

    public static function fromPlural(string $plural): ?self
    {
        return match ($plural) {
            'properties' => self::PROPERTY,
            'stays' => self::STAY,
            'vehicles' => self::VEHICLE,
            'taxis' => self::TAXI,
            'events' => self::EVENT,
            'smes' => self::SME,
            'experiences' => self::EXPERIENCE,
            default => null,
        };
    }
}
