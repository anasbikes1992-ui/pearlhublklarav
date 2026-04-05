<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxiRideFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id'       => User::factory()->state(['role' => 'customer']),
            'status'            => 'searching',
            'pickup_latitude'   => $this->faker->latitude(5.9, 9.9),
            'pickup_longitude'  => $this->faker->longitude(79.6, 81.9),
            'dropoff_latitude'  => $this->faker->latitude(5.9, 9.9),
            'dropoff_longitude' => $this->faker->longitude(79.6, 81.9),
            'currency'          => 'LKR',
        ];
    }
}
