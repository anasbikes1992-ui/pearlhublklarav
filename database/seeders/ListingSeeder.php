<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $provider = User::query()->where('role', 'provider')->first()
            ?? User::factory()->create([
                'full_name' => 'Demo Provider',
                'email'     => 'demo.provider@pearlhub.lk',
                'role'      => 'provider',
                'password'  => bcrypt('secret123'),
            ]);

        $customer = User::query()->where('role', 'customer')->first()
            ?? User::factory()->create([
                'full_name' => 'Demo Customer',
                'email'     => 'demo.customer@pearlhub.lk',
                'role'      => 'customer',
                'password'  => bcrypt('secret123'),
            ]);

        // Create demo listings for each vertical
        $verticals = ['property', 'stay', 'vehicle', 'event', 'sme'];

        foreach ($verticals as $vertical) {
            Listing::factory()
                ->count(4)
                ->state(['provider_id' => $provider->id, 'vertical' => $vertical])
                ->create()
                ->each(function (Listing $listing) use ($customer): void {
                    // Seed 2-3 reviews per listing
                    Review::factory()
                        ->count(fake()->numberBetween(2, 3))
                        ->state(['listing_id' => $listing->id, 'reviewer_id' => $customer->id])
                        ->create();
                });
        }

        $this->command->info('Seeded ' . (count($verticals) * 4) . ' listings with reviews.');
    }
}
