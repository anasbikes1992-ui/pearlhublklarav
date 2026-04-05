<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use App\Models\Profile;
use App\Models\User;
use App\Models\VerticalFeeConfig;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoreSchemaSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['full_name' => 'Admin User', 'email' => 'admin@pearlhub.lk', 'role' => User::ROLE_ADMIN],
            ['full_name' => 'Provider One', 'email' => 'provider1@pearlhub.lk', 'role' => User::ROLE_PROVIDER],
            ['full_name' => 'Provider Two', 'email' => 'provider2@pearlhub.lk', 'role' => User::ROLE_PROVIDER],
            ['full_name' => 'Customer One', 'email' => 'customer1@pearlhub.lk', 'role' => User::ROLE_CUSTOMER],
            ['full_name' => 'Customer Two', 'email' => 'customer2@pearlhub.lk', 'role' => User::ROLE_CUSTOMER],
            ['full_name' => 'Customer Three', 'email' => 'customer3@pearlhub.lk', 'role' => User::ROLE_CUSTOMER],
            ['full_name' => 'Driver One', 'email' => 'driver1@pearlhub.lk', 'role' => User::ROLE_DRIVER],
            ['full_name' => 'Driver Two', 'email' => 'driver2@pearlhub.lk', 'role' => User::ROLE_DRIVER],
            ['full_name' => 'Broker One', 'email' => 'broker1@pearlhub.lk', 'role' => 'broker'],
        ];

        foreach ($users as $index => $seedUser) {
            $user = User::query()->updateOrCreate(
                ['email' => $seedUser['email']],
                [
                    'full_name' => $seedUser['full_name'],
                    'role' => $seedUser['role'],
                    'phone' => sprintf('+94771234%03d', $index),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password' => Hash::make('secret123'),
                ]
            );

            Profile::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nic' => 'NIC-' . str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'address_line_1' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'district' => fake()->randomElement(['Colombo', 'Gampaha', 'Kandy', 'Galle']),
                    'country_code' => 'LK',
                    'is_kyc_verified' => $seedUser['role'] !== User::ROLE_CUSTOMER,
                ]
            );

            Wallet::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'balance' => fake()->randomFloat(2, 2500, 50000),
                    'currency' => 'LKR',
                    'status' => 'active',
                ]
            );
        }

        $platformSettings = [
            [
                'key' => 'booking_commission_rate',
                'value' => ['value' => 0.08],
                'description' => 'Default commission rate used for booking totals.',
            ],
            [
                'key' => 'property_listing_fee',
                'value' => ['value' => 0],
                'description' => 'Configurable property listing fee placeholder.',
            ],
            [
                'key' => 'buyer_cashback_rate',
                'value' => ['value' => 0.005],
                'description' => 'Default buyer cashback rate for promo-confirmed sales.',
            ],
        ];

        foreach ($platformSettings as $setting) {
            PlatformSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'description' => $setting['description'],
                ]
            );
        }

        $feeConfigByVertical = [
            'property' => [0, 0.02, 0, 0, 0],
            'stay' => [0, 0.08, 0.15, 0.01, 0.10],
            'vehicle' => [0, 0.05, 0.15, 0, 0],
            'taxi' => [0, 0.10, 0.15, 0, 0],
            'event' => [0, 0.05, 0.15, 0, 0],
            'sme' => [0, 0.03, 0.15, 0, 0],
            'experience' => [0, 0.08, 0.15, 0.01, 0.05],
        ];

        foreach ($feeConfigByVertical as $vertical => [$listingFee, $commissionRate, $vatRate, $tourismRate, $serviceRate]) {
            VerticalFeeConfig::query()->updateOrCreate(
                ['vertical' => $vertical],
                [
                    'listing_fee' => $listingFee,
                    'commission_rate' => $commissionRate,
                    'vat_rate' => $vatRate,
                    'tourism_tax_rate' => $tourismRate,
                    'service_charge_rate' => $serviceRate,
                    'is_active' => true,
                ]
            );
        }
    }
}
