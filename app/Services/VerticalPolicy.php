<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
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
            'buffer_hours' => 0,
        ],
        'stay' => [
            'commission_rate' => 0.09,
            'tax_rate' => 0.18,
            'flow_type' => 'booking',
            'cancellation_window_hours' => 24,
            'requires_escrow' => true,
            'product_limit' => null,
            'buffer_hours' => 2,
        ],
        'vehicle' => [
            'commission_rate' => 0.08,
            'tax_rate' => 0.18,
            'flow_type' => 'booking',
            'cancellation_window_hours' => 24,
            'requires_escrow' => true,
            'product_limit' => null,
            'buffer_hours' => 1,
        ],
        'taxi' => [
            'commission_rate' => 0.12,
            'tax_rate' => 0.18,
            'flow_type' => 'booking',
            'cancellation_window_hours' => 1,
            'requires_escrow' => false,
            'product_limit' => null,
            'buffer_hours' => 0,
        ],
        'event' => [
            'commission_rate' => 0.1,
            'tax_rate' => 0.18,
            'flow_type' => 'booking',
            'cancellation_window_hours' => 72,
            'requires_escrow' => true,
            'product_limit' => null,
            'buffer_hours' => 0,
        ],
        'sme' => [
            'commission_rate' => 0,
            'tax_rate' => 0.18,
            'flow_type' => 'inquiry_only',
            'cancellation_window_hours' => 0,
            'requires_escrow' => false,
            'product_limit' => 100,
            'buffer_hours' => 0,
        ],
        'experience' => [
            'commission_rate' => 0.15,
            'tax_rate' => 0.18,
            'flow_type' => 'booking',
            'cancellation_window_hours' => 48,
            'requires_escrow' => true,
            'product_limit' => null,
            'buffer_hours' => 4,
        ],
    ];

    /**
     * Cache TTL in seconds (1 hour)
     */
    private const CACHE_TTL = 3600;

    /**
     * Mapping from frontend plural names to backend singular names
     * @var array<string, string>
     */
    private array $verticalAliases = [
        // Frontend plural -> Backend singular
        'properties' => 'property',
        'stays' => 'stay',
        'vehicles' => 'vehicle',
        'events' => 'event',
        'experiences' => 'experience',
        // Backend singular (pass-through)
        'property' => 'property',
        'stay' => 'stay',
        'vehicle' => 'vehicle',
        'event' => 'event',
        'experience' => 'experience',
        'taxi' => 'taxi',
        'sme' => 'sme',
    ];

    /**
     * Normalize vertical name to backend format
     */
    public function normalizeVertical(string $vertical): string
    {
        $vertical = strtolower($vertical);
        return $this->verticalAliases[$vertical] ?? $vertical;
    }

    /**
     * Get all supported verticals (backend format)
     * @return array<string>
     */
    public function getSupportedVerticals(): array
    {
        return array_keys($this->rules);
    }

    /**
     * Check if vertical is supported
     */
    public function isSupported(string $vertical): bool
    {
        $normalized = $this->normalizeVertical($vertical);
        return isset($this->rules[$normalized]);
    }

    /**
     * @return array<string, mixed>
     */
    public function forVertical(string $vertical): array
    {
        $vertical = $this->normalizeVertical($vertical);
        
        return Cache::remember("vertical_policy:{$vertical}", self::CACHE_TTL, function () use ($vertical): array {
            if (! isset($this->rules[$vertical])) {
                throw new InvalidArgumentException("Unsupported vertical [{$vertical}].");
            }

            return $this->rules[$vertical];
        });
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

    public function getCancellationWindowHours(string $vertical): int
    {
        return (int) Arr::get($this->forVertical($vertical), 'cancellation_window_hours', 24);
    }

    public function getBufferHours(string $vertical): int
    {
        return (int) Arr::get($this->forVertical($vertical), 'buffer_hours', 0);
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

    /**
     * Clear the cached policy for a vertical
     */
    public function clearCache(string $vertical): void
    {
        Cache::forget("vertical_policy:{$vertical}");
    }

    /**
     * Clear all vertical policy caches
     */
    public function clearAllCache(): void
    {
        foreach ($this->rules as $vertical => $_) {
            Cache::forget("vertical_policy:{$vertical}");
        }
    }
}
