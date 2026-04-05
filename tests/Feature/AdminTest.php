<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_get_platform_stats(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/stats');

        $response->assertOk();
        $response->assertJsonStructure(['data' => [
            'total_users',
            'total_listings',
            'pending_verifications',
            'platform_revenue',
            'bookings_30d',
            'pending_kyc',
            'flagged_posts',
            'bookings_by_vertical',
        ]]);
    }

    public function test_non_admin_cannot_access_stats(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/admin/stats');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_stats(): void
    {
        $response = $this->getJson('/api/v1/admin/stats');

        $response->assertStatus(401);
    }

    public function test_admin_can_list_users(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->count(3)->create(['role' => 'customer']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/users');

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['data', 'total']]);
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->count(2)->create(['role' => 'customer']);
        User::factory()->count(1)->create(['role' => 'provider']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/users?role=customer');

        $response->assertOk();
        $users = $response->json('data.data');
        foreach ($users as $user) {
            $this->assertEquals('customer', $user['role']);
        }
    }

    public function test_non_admin_cannot_list_users(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);

        $response = $this->actingAs($provider, 'sanctum')
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_deactivate_user(): void
    {
        $admin = $this->makeAdmin();
        $user = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user->id}", [
                'is_active' => false,
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
    }

    public function test_admin_can_reactivate_user(): void
    {
        $admin = $this->makeAdmin();
        $user = User::factory()->create(['role' => 'customer', 'is_active' => false]);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user->id}", [
                'is_active' => true,
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => true]);
    }

    public function test_non_admin_cannot_update_user(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $target = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer, 'sanctum')
            ->putJson("/api/v1/admin/users/{$target->id}", [
                'is_active' => false,
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_stats_counts_published_listings(): void
    {
        $admin = $this->makeAdmin();
        $provider = User::factory()->create(['role' => 'provider']);
        Listing::factory()->count(2)->create([
            'provider_id' => $provider->id,
            'status'      => 'published',
        ]);
        Listing::factory()->create([
            'provider_id' => $provider->id,
            'status'      => 'draft',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/stats');

        $response->assertOk();
        $this->assertEquals(2, $response->json('data.total_listings'));
    }

    public function test_admin_update_user_requires_is_active_field(): void
    {
        $admin = $this->makeAdmin();
        $user = User::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user->id}", []);

        $response->assertStatus(422);
    }
}
