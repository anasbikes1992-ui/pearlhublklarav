<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_public_listings(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        Listing::factory()->count(3)->create([
            'provider_id' => $provider->id,
            'status' => 'published',
            'is_hidden' => false,
        ]);

        $response = $this->getJson('/api/v1/listings');

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_can_show_single_listing(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $listing = Listing::factory()->create([
            'provider_id' => $provider->id,
            'status' => 'published',
        ]);

        $response = $this->getJson("/api/v1/listings/{$listing->id}");

        $response->assertOk();
        $response->assertJsonFragment(['title' => $listing->title]);
    }

    public function test_provider_can_create_listing(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);

        $response = $this->actingAs($provider, 'sanctum')
            ->postJson('/api/v1/listings', [
                'title' => 'Luxury Villa in Galle',
                'description' => 'A beautiful beachfront villa.',
                'price' => 45000000,
                'vertical' => 'property',
                'currency' => 'LKR',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('listings', ['title' => 'Luxury Villa in Galle']);
    }

    public function test_provider_can_view_own_listings(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        Listing::factory()->count(2)->create(['provider_id' => $provider->id]);

        $response = $this->actingAs($provider, 'sanctum')
            ->getJson('/api/v1/listings/my');

        $response->assertOk();
    }

    public function test_unauthenticated_cannot_create_listing(): void
    {
        $response = $this->postJson('/api/v1/listings', [
            'title' => 'Unauthorized Listing',
            'description' => 'Should fail.',
            'price' => 1000,
            'vertical' => 'stay',
        ]);

        $response->assertStatus(401);
    }

    public function test_provider_can_update_listing(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $listing = Listing::factory()->create(['provider_id' => $provider->id]);

        $response = $this->actingAs($provider, 'sanctum')
            ->putJson("/api/v1/listings/{$listing->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('listings', ['id' => $listing->id, 'title' => 'Updated Title']);
    }

    public function test_provider_can_delete_listing(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $listing = Listing::factory()->create(['provider_id' => $provider->id]);

        $response = $this->actingAs($provider, 'sanctum')
            ->deleteJson("/api/v1/listings/{$listing->id}");

        $response->assertOk();
    }
}
