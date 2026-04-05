<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create([
            'full_name' => 'Nimal Perera',
            'email'     => 'nimal@pearlhub.lk',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/users/profile');

        $response->assertOk();
        $response->assertJsonFragment(['email' => 'nimal@pearlhub.lk', 'full_name' => 'Nimal Perera']);
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/users/profile');

        $response->assertStatus(401);
    }

    public function test_user_can_update_full_name(): void
    {
        $user = User::factory()->create(['full_name' => 'Old Name']);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/users/profile', [
                'full_name' => 'New Name',
            ]);

        $response->assertOk();
        $response->assertJsonFragment(['full_name' => 'New Name']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'full_name' => 'New Name']);
    }

    public function test_user_can_update_phone(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/users/profile', [
                'phone' => '+94771234567',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'phone' => '+94771234567']);
    }

    public function test_user_can_update_profile_details(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/users/profile', [
                'full_name' => 'Amal Silva',
                'profile'   => [
                    'city'         => 'Colombo',
                    'district'     => 'Colombo',
                    'country_code' => 'LK',
                ],
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('profiles', [
            'user_id'  => $user->id,
            'city'     => 'Colombo',
            'country_code' => 'LK',
        ]);
    }

    public function test_full_name_max_length_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/users/profile', [
                'full_name' => str_repeat('A', 121),
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['full_name']]);
    }

    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $response = $this->putJson('/api/v1/users/profile', [
            'full_name' => 'Hacker',
        ]);

        $response->assertStatus(401);
    }

    public function test_profile_includes_nested_profile_relation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/users/profile');

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['id', 'full_name', 'email', 'profile']]);
    }

    public function test_profile_update_returns_fresh_user_data(): void
    {
        $user = User::factory()->create(['full_name' => 'Before']);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/users/profile', [
                'full_name' => 'After',
            ]);

        $response->assertOk();
        $response->assertJsonFragment(['full_name' => 'After']);
    }
}
