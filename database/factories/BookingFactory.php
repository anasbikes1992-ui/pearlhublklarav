<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'listing_id'     => Listing::factory()->state(['vertical' => 'stay']),
            'customer_id'    => User::factory()->state(['role' => 'customer']),
            'status'         => 'pending',
            'total_amount'   => $this->faker->randomFloat(2, 1000, 50000),
            'currency'       => 'LKR',
            'payment_status' => 'pending',
        ];
    }
}
