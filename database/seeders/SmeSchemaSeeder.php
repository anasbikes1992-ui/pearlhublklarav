<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\ProviderSalesReport;
use App\Models\SmeProduct;
use App\Models\SmeSubscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class SmeSchemaSeeder extends Seeder
{
    public function run(): void
    {
        $providers = User::query()->where('role', User::ROLE_PROVIDER)->get();

        foreach ($providers as $provider) {
            SmeSubscription::query()->updateOrCreate(
                ['provider_id' => $provider->id],
                [
                    'plan' => fake()->randomElement(['silver', 'gold', 'platinum']),
                    'expires_at' => now()->addMonths(fake()->numberBetween(1, 10)),
                    'product_limit' => fake()->randomElement([10, 25, 50]),
                    'status' => 'active',
                ]
            );

            ProviderSalesReport::query()->updateOrCreate(
                [
                    'provider_id' => $provider->id,
                    'month' => now()->startOfMonth()->toDateString(),
                ],
                [
                    'total_sales' => fake()->randomFloat(2, 250000, 2500000),
                    'commission_due' => fake()->randomFloat(2, 15000, 200000),
                    'tax_applied' => fake()->randomFloat(2, 5000, 100000),
                    'verified' => fake()->boolean(70),
                ]
            );
        }

        $smeListings = Listing::query()->where('vertical', 'sme')->get();
        foreach ($smeListings as $listing) {
            foreach (range(1, 2) as $index) {
                SmeProduct::query()->firstOrCreate(
                    [
                        'listing_id' => $listing->id,
                        'name' => $listing->title . ' Product ' . $index,
                    ],
                    [
                        'category' => fake()->randomElement(['craft', 'food', 'wellness', 'tour', 'service']),
                        'description' => fake()->sentence(20),
                        'price' => fake()->randomFloat(2, 1200, 45000),
                        'variants' => [
                            ['name' => 'Standard', 'price_delta' => 0],
                            ['name' => 'Premium', 'price_delta' => 1500],
                        ],
                        'is_active' => true,
                        'stock_status' => fake()->randomElement(['in_stock', 'out_of_stock']),
                    ]
                );
            }
        }
    }
}
