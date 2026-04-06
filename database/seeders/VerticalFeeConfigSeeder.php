<?php

namespace Database\Seeders;

use App\Models\VerticalFeeConfig;
use Illuminate\Database\Seeder;

class VerticalFeeConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [
                'vertical' => 'property',
                'display_name' => 'Properties',
                'icon' => '🏠',
                'color' => 'teal',
                'listing_fee' => 0,
                'commission_rate' => 0.06,
                'vat_rate' => 0.18,
                'tourism_tax_rate' => 0,
                'service_charge_rate' => 0.02,
                'flow_type' => 'booking',
                'cancellation_window_hours' => 48,
                'buffer_hours' => 0,
                'requires_escrow' => true,
                'product_limit' => null,
                'is_active' => true,
            ],
            [
                'vertical' => 'stay',
                'display_name' => 'Luxury Stays',
                'icon' => '🏨',
                'color' => 'gold',
                'listing_fee' => 0,
                'commission_rate' => 0.09,
                'vat_rate' => 0.18,
                'tourism_tax_rate' => 0.01,
                'service_charge_rate' => 0.02,
                'flow_type' => 'booking',
                'cancellation_window_hours' => 24,
                'buffer_hours' => 2,
                'requires_escrow' => true,
                'product_limit' => null,
                'is_active' => true,
            ],
            [
                'vertical' => 'vehicle',
                'display_name' => 'Vehicles',
                'icon' => '🚗',
                'color' => 'emerald',
                'listing_fee' => 0,
                'commission_rate' => 0.08,
                'vat_rate' => 0.18,
                'tourism_tax_rate' => 0,
                'service_charge_rate' => 0.02,
                'flow_type' => 'booking',
                'cancellation_window_hours' => 24,
                'buffer_hours' => 1,
                'requires_escrow' => true,
                'product_limit' => null,
                'is_active' => true,
            ],
            [
                'vertical' => 'taxi',
                'display_name' => 'Taxi Rides',
                'icon' => '🚕',
                'color' => 'amber',
                'listing_fee' => 0,
                'commission_rate' => 0.12,
                'vat_rate' => 0.18,
                'tourism_tax_rate' => 0,
                'service_charge_rate' => 0.01,
                'flow_type' => 'booking',
                'cancellation_window_hours' => 1,
                'buffer_hours' => 0,
                'requires_escrow' => false,
                'product_limit' => null,
                'is_active' => true,
            ],
            [
                'vertical' => 'event',
                'display_name' => 'Events',
                'icon' => '🎉',
                'color' => 'rose',
                'listing_fee' => 0,
                'commission_rate' => 0.10,
                'vat_rate' => 0.18,
                'tourism_tax_rate' => 0,
                'service_charge_rate' => 0.02,
                'flow_type' => 'booking',
                'cancellation_window_hours' => 72,
                'buffer_hours' => 0,
                'requires_escrow' => true,
                'product_limit' => null,
                'is_active' => true,
            ],
            [
                'vertical' => 'experience',
                'display_name' => 'Experiences',
                'icon' => '✨',
                'color' => 'purple',
                'listing_fee' => 0,
                'commission_rate' => 0.15,
                'vat_rate' => 0.18,
                'tourism_tax_rate' => 0.02,
                'service_charge_rate' => 0.02,
                'flow_type' => 'booking',
                'cancellation_window_hours' => 48,
                'buffer_hours' => 4,
                'requires_escrow' => true,
                'product_limit' => null,
                'is_active' => true,
            ],
            [
                'vertical' => 'sme',
                'display_name' => 'SME Business',
                'icon' => '🏢',
                'color' => 'blue',
                'listing_fee' => 25000, // Annual subscription base
                'commission_rate' => 0,
                'vat_rate' => 0.18,
                'tourism_tax_rate' => 0,
                'service_charge_rate' => 0,
                'flow_type' => 'inquiry_only',
                'cancellation_window_hours' => 0,
                'buffer_hours' => 0,
                'requires_escrow' => false,
                'product_limit' => 100,
                'is_active' => true,
            ],
        ];

        foreach ($configs as $config) {
            VerticalFeeConfig::query()->updateOrCreate(
                ['vertical' => $config['vertical']],
                $config
            );
        }
    }
}
