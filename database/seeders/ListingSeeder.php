<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create();

        $provider = User::query()->where('role', 'provider')->first()
            ?? User::factory()->create([
                'full_name' => 'Demo Provider',
                'email'     => 'demo.provider@pearlhub.lk',
                'role'      => 'provider',
                'password'  => bcrypt('secret123'),
            ]);

        $customers = User::query()->where('role', 'customer')->get();

        if ($customers->count() < 3) {
            $needed = 3 - $customers->count();

            $generatedCustomers = User::factory()
                ->count($needed)
                ->state(['role' => 'customer'])
                ->create();

            $customers = $customers->concat($generatedCustomers);
        }

        // Create demo listings for each vertical
        $verticals = ['property', 'stay', 'vehicle', 'taxi', 'event', 'sme'];

        foreach ($verticals as $vertical) {
            Listing::factory()
                ->count(4)
                ->state(['provider_id' => $provider->id, 'vertical' => $vertical])
                ->create()
                ->each(function (Listing $listing) use ($customers, $faker): void {
                    // Seed 2-3 reviews per listing
                    $reviewers = $customers->shuffle()->take($faker->numberBetween(2, 3));

                    foreach ($reviewers as $reviewer) {
                        Review::factory()->create([
                            'listing_id' => $listing->id,
                            'reviewer_id' => $reviewer->id,
                        ]);
                    }
                });
        }

        $this->command->info('Seeded ' . (count($verticals) * 4) . ' listings with reviews.');
    }
}
