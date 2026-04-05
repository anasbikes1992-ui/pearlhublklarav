<?php

namespace App\Services;

use Illuminate\Support\Arr;
use InvalidArgumentException;

class VerticalPolicy
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $rules = [
        'property' => [
            'commission_rate' => 0.06,
            'tax_rate' => 0.18,
            'flow_type' => 'booking',
            'cancellation_window_hours' => 48,
            'requires_escrow' => true,
            'product_limit' => null,
        ],
        'stay' => [
            'commission_rate' => 0.09,
            'tax_rate' => 0.18,
            'flow_type' => 'booking',
            'cancellation_window_hours' => 24,
            'requires_escrow' => true,
            'product_limit' => null,
        ],
        'vehicle' => [
            'commission_rate' => 0.08,
            'tax_rate' => 0.18,
            'flow_type' => 'booking',
            'cancellation_window_hours' => 24,
            'requires_escrow' => true,
            'product_limit' => null,
        ],
        'taxi' => [
            'commission_rate' => 0.12,
            'tax_rate' => 0.18,
            'flow_type' => 'booking',
            'cancellation_window_hours' => 1,
            'requires_escrow' => false,
            'product_limit' => null,
        ],
        'event' => [
            'commission_rate' => 0.1,
            'tax_rate' => 0.18,
            'flow_type' => 'booking',
            'cancellation_window_hours' => 72,
            'requires_escrow' => true,
            'product_limit' => null,
        ],
        'sme' => [
            'commission_rate' => 0,
            'tax_rate' => 0.18,
            'flow_type' => 'inquiry_only',
            'cancellation_window_hours' => 0,
            'requires_escrow' => false,
            'product_limit' => 100,
        ],
    ];

    /**
     * @return array<string, mixed>
     */
    public function forVertical(string $vertical): array
    {
        $vertical = strtolower($vertical);
        if (! isset($this->rules[$vertical])) {
            throw new InvalidArgumentException("Unsupported vertical [{$vertical}].");
        }

        return $this->rules[$vertical];
    }

    public function commissionRate(string $vertical): float
    {
        return (float) Arr::get($this->forVertical($vertical), 'commission_rate', 0);
    }

    public function taxRate(string $vertical): float
    {
        return (float) Arr::get($this->forVertical($vertical), 'tax_rate', 0);
    }

    public function isInquiryOnly(string $vertical): bool
    {
        return Arr::get($this->forVertical($vertical), 'flow_type') === 'inquiry_only';
    }

    public function requiresEscrow(string $vertical): bool
    {
        return (bool) Arr::get($this->forVertical($vertical), 'requires_escrow', true);
    }

    public function getProductLimit(string $vertical): ?int
    {
        $value = Arr::get($this->forVertical($vertical), 'product_limit');

        return is_null($value) ? null : (int) $value;
    }

    public function forSmePlan(string $plan): array
    {
        return match (strtolower($plan)) {
            'silver' => ['limit' => 100, 'price_lkr' => 25000, 'features' => ['store_profile', 'catalog']],
            'gold' => ['limit' => 500, 'price_lkr' => 50000, 'features' => ['store_profile', 'catalog', 'insights']],
            'platinum' => ['limit' => 0, 'price_lkr' => 65000, 'features' => ['store_profile', 'catalog', 'insights', 'variants', 'bulk_upload']],
            default => throw new InvalidArgumentException("Unsupported subscription plan [{$plan}]."),
        };
    }
}
