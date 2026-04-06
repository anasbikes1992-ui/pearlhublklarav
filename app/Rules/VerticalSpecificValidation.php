<?php

namespace App\Rules;

use App\Models\Listing;
use App\Services\VerticalPolicy;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class VerticalSpecificValidation implements ValidationRule
{
    public function __construct(
        private readonly string $vertical,
        private readonly string $attributeType, // 'listing', 'booking', 'pricing'
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $policy = app(VerticalPolicy::class);
        $normalizedVertical = $policy->normalizeVertical($this->vertical);

        match ($this->attributeType) {
            'listing' => $this->validateListing($attribute, $value, $fail, $normalizedVertical),
            'booking' => $this->validateBooking($attribute, $value, $fail, $normalizedVertical),
            'pricing' => $this->validatePricing($attribute, $value, $fail, $normalizedVertical),
            default => null,
        };
    }

    private function validateListing(string $attribute, mixed $value, Closure $fail, string $vertical): void
    {
        // Property-specific validations
        if ($vertical === 'property' && $attribute === 'metadata.ownership_type') {
            $validTypes = ['freehold', 'leasehold', 'condominium', 'cooperative'];
            if (!in_array($value, $validTypes)) {
                $fail("Invalid ownership type for property.");
            }
        }

        // Stay-specific validations
        if ($vertical === 'stay' && $attribute === 'metadata.check_in_time') {
            if (!preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $value)) {
                $fail("Check-in time must be in HH:MM format.");
            }
        }

        // Vehicle-specific validations
        if ($vertical === 'vehicle' && $attribute === 'metadata.transmission') {
            $validTypes = ['automatic', 'manual', 'cvt'];
            if (!in_array($value, $validTypes)) {
                $fail("Invalid transmission type.");
            }
        }

        // Event-specific validations
        if ($vertical === 'event' && $attribute === 'metadata.event_date') {
            $eventDate = \Carbon\Carbon::parse($value);
            if ($eventDate->isPast()) {
                $fail("Event date must be in the future.");
            }
        }

        // Experience-specific validations
        if ($vertical === 'experience' && $attribute === 'metadata.duration_hours') {
            if ($value < 1 || $value > 72) {
                $fail("Experience duration must be between 1 and 72 hours.");
            }
        }

        // SME-specific validations
        if ($vertical === 'sme' && $attribute === 'metadata.business_category') {
            $validCategories = ['retail', 'food', 'services', 'manufacturing', 'technology'];
            if (!in_array($value, $validCategories)) {
                $fail("Invalid business category.");
            }
        }
    }

    private function validateBooking(string $attribute, mixed $value, Closure $fail, string $vertical): void
    {
        $policy = app(VerticalPolicy::class);

        if ($attribute === 'start_at') {
            $startDate = \Carbon\Carbon::parse($value);
            $bufferHours = $policy->getBufferHours($vertical);

            $minimumStart = now()->addHours($bufferHours);
            if ($startDate->isBefore($minimumStart)) {
                $fail("Bookings must be at least {$bufferHours} hours in advance for this vertical.");
            }
        }

        // Property: minimum stay duration
        if ($vertical === 'property' && $attribute === 'duration_days') {
            if ($value < 30) {
                $fail("Property rentals require minimum 30 days.");
            }
        }

        // Vehicle: minimum rental duration
        if ($vertical === 'vehicle' && $attribute === 'duration_hours') {
            if ($value < 4) {
                $fail("Vehicle rentals require minimum 4 hours.");
            }
        }

        // Taxi: immediate booking only
        if ($vertical === 'taxi' && $attribute === 'start_at') {
            $startDate = \Carbon\Carbon::parse($value);
            if ($startDate->isAfter(now()->addHours(2))) {
                $fail("Taxi bookings must be within 2 hours.");
            }
        }
    }

    private function validatePricing(string $attribute, mixed $value, Closure $fail, string $vertical): void
    {
        // Minimum pricing by vertical
        $minimums = [
            'property' => 50000,    // LKR 50,000
            'stay' => 3000,       // LKR 3,000 per night
            'vehicle' => 2000,    // LKR 2,000 per day
            'taxi' => 500,        // LKR 500 base
            'event' => 10000,     // LKR 10,000
            'experience' => 5000, // LKR 5,000
            'sme' => 100,         // LKR 100
        ];

        if ($attribute === 'price' && isset($minimums[$vertical])) {
            if ($value < $minimums[$vertical]) {
                $fail("Minimum price for {$vertical} is LKR " . number_format($minimums[$vertical]) . ".");
            }
        }
    }
}
