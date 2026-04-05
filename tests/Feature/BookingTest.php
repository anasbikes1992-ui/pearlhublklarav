<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_booking(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $customer = User::factory()->create(['role' => 'customer']);
        $listing = Listing::factory()->create([
            'provider_id' => $provider->id,
            'status' => 'published',
            'vertical' => 'stay',
            'price' => 5000,
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/bookings', [
                'listing_id' => $listing->id,
                'notes' => 'Test booking',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', [
            'listing_id' => $listing->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_customer_can_list_own_bookings(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $listing = Listing::factory()->create([
            'provider_id' => $provider->id,
            'vertical' => 'stay',
        ]);

        Booking::factory()->count(2)->create([
            'customer_id' => $customer->id,
            'listing_id' => $listing->id,
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/bookings');

        $response->assertOk();
    }

    public function test_unauthenticated_cannot_create_booking(): void
    {
        $response = $this->postJson('/api/v1/bookings', [
            'listing_id' => fake()->uuid(),
        ]);

        $response->assertStatus(401);
    }

    public function test_booking_creates_escrow_record(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $customer = User::factory()->create(['role' => 'customer']);
        $listing = Listing::factory()->create([
            'provider_id' => $provider->id,
            'status' => 'published',
            'vertical' => 'stay',
            'price' => 10000,
        ]);

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/bookings', [
                'listing_id' => $listing->id,
            ]);

        $this->assertDatabaseCount('escrows', 1);
    }
}
