<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'listing_id'  => Listing::factory(),
            'reviewer_id' => User::factory()->state(['role' => 'customer']),
            'rating'      => $this->faker->randomFloat(1, 3.0, 5.0),
            'comment'     => $this->faker->sentences(2, true),
        ];
    }
}
