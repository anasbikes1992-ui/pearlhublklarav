<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocialPostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'content'        => $this->faker->paragraph(),
            'media_urls'     => [],
            'vertical_tag'   => $this->faker->randomElement(['property', 'stays', 'vehicles', 'events', 'social']),
            'likes_count'    => 0,
            'comments_count' => 0,
            'is_pinned'      => false,
            'is_flagged'     => false,
        ];
    }
}
