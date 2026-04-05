<?php

namespace Tests\Feature;

use App\Models\TaxiRide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxiRideTest extends TestCase
{
    use RefreshDatabase;

    private array $validRidePayload = [
        'pickup_latitude'   => 6.9271,
        'pickup_longitude'  => 79.8612,
        'dropoff_latitude'  => 7.2906,
        'dropoff_longitude' => 80.6337,
    ];

    public function test_authenticated_user_can_create_taxi_ride(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/taxi-rides', $this->validRidePayload);

        $response->assertStatus(201);
        $response->assertJsonFragment(['status' => 'searching']);
        $this->assertDatabaseHas('taxi_rides', ['customer_id' => $customer->id]);
    }

    public function test_unauthenticated_user_cannot_create_taxi_ride(): void
    {
        $response = $this->postJson('/api/v1/taxi-rides', $this->validRidePayload);

        $response->assertStatus(401);
    }

    public function test_taxi_ride_requires_valid_coordinates(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/taxi-rides', [
                'pickup_latitude'  => 999,
                'pickup_longitude' => 999,
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors']);
    }

    public function test_taxi_ride_requires_all_coordinate_fields(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/taxi-rides', [
                'pickup_latitude'  => 6.9271,
                'pickup_longitude' => 79.8612,
                // missing dropoff
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['dropoff_latitude']]);
    }

    public function test_authenticated_user_can_list_own_taxi_rides(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        TaxiRide::factory()->count(2)->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/taxi-rides');

        $response->assertOk();
        $response->assertJsonStructure(['data']);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_user_cannot_see_other_users_rides(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        TaxiRide::factory()->count(3)->create(['customer_id' => $customer2->id]);

        $response = $this->actingAs($customer1, 'sanctum')
            ->getJson('/api/v1/taxi-rides');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }

    public function test_authenticated_user_can_show_taxi_ride(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $ride = TaxiRide::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson("/api/v1/taxi-rides/{$ride->id}");

        $response->assertOk();
        $response->assertJsonFragment(['id' => $ride->id]);
    }

    public function test_taxi_ride_status_can_be_updated(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $ride = TaxiRide::factory()->create([
            'customer_id' => $customer->id,
            'status'      => 'searching',
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->putJson("/api/v1/taxi-rides/{$ride->id}", [
                'status' => 'cancelled',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('taxi_rides', ['id' => $ride->id, 'status' => 'cancelled']);
    }

    public function test_taxi_ride_status_must_be_valid(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $ride = TaxiRide::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($customer, 'sanctum')
            ->putJson("/api/v1/taxi-rides/{$ride->id}", [
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422);
    }

    public function test_taxi_ride_creation_sets_searching_status(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/taxi-rides', $this->validRidePayload);

        $this->assertDatabaseHas('taxi_rides', [
            'customer_id' => $customer->id,
            'status'      => 'searching',
        ]);
    }
}
