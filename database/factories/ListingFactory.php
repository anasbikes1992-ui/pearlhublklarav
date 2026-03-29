<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ListingFactory extends Factory
{
    private static array $titles = [
        'property' => [
            'Luxury 4-Bedroom Villa in Colombo 07',
            'Heritage Bungalow with Ocean Views in Galle',
            'Modern Apartment in Colombo City Centre',
            'Tea Estate Bungalow in Nuwara Eliya',
            'Investment-Grade Apartment in Rajagiriya',
            'Colonial Mansion in Kandy',
        ],
        'stay' => [
            'Boutique Hotel Suite in Ella',
            'Beach Front Villa in Mirissa',
            'Eco Lodge in Sinharaja Forest',
            'Clifftop Cabana in Tangalle',
            'Heritage Guesthouse in Galle Fort',
            'Treehouse Retreat in Kandy',
        ],
        'vehicle' => [
            'Mercedes S-Class with Personal Driver',
            'Luxury 4WD Land Cruiser with Chauffeur',
            'Vintage Defender Island Tour',
            'Premium Tuk-Tuk City Experience',
            'Tesla Model 3 Self-Drive',
            'Executive Van for Corporate Travel',
        ],
        'event' => [
            'Galle Literary Festival VIP Package',
            'Kandy Esala Perahera Reserved Seating',
            'Colombo Jazz Festival Front Row',
            'Traditional Kandyan Dance Show',
            'Vesak Festival Cultural Tour',
            'New Year Avurudu Village Festival',
        ],
        'sme' => [
            'Traditional Batik Workshop — Colombo',
            'Authentic Lanka Kitchen Cooking Class',
            'Ayurveda Wellness & Spa Day Package',
            'Ceylon Tea Tasting Experience',
            'Gem Cutting & Jewellery Workshop',
            'Handloom Silk Weaving Demonstration',
        ],
    ];

    public function definition(): array
    {
        $vertical = $this->faker->randomElement(['property', 'stay', 'vehicle', 'event', 'sme']);
        $title = $this->faker->randomElement(self::$titles[$vertical]);

        return [
            'provider_id' => User::factory()->state(['role' => 'provider']),
            'vertical'    => $vertical,
            'title'       => $title,
            'slug'        => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(100, 999),
            'description' => $this->faker->paragraphs(2, true),
            'price'       => match ($vertical) {
                'property' => $this->faker->randomFloat(2, 5_000_000, 80_000_000),
                'stay'     => $this->faker->randomFloat(2, 8_000, 80_000),
                'vehicle'  => $this->faker->randomFloat(2, 5_000, 40_000),
                'event'    => $this->faker->randomFloat(2, 2_000, 25_000),
                'sme'      => $this->faker->randomFloat(2, 1_500, 15_000),
            },
            'currency'    => 'LKR',
            'status'      => 'published',
            'is_hidden'   => false,
            'latitude'    => $this->faker->latitude(5.9, 9.9),
            'longitude'   => $this->faker->longitude(79.6, 81.9),
            'verified_at' => now()->subDays($this->faker->numberBetween(1, 90)),
        ];
    }

    public function pending(): self
    {
        return $this->state(['status' => 'pending_verification', 'is_hidden' => true]);
    }

    public function property(): self
    {
        return $this->state(['vertical' => 'property']);
    }

    public function stay(): self
    {
        return $this->state(['vertical' => 'stay']);
    }
}
