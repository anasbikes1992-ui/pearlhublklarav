<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_reviews_for_listing(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $listing = Listing::factory()->create(['provider_id' => $provider->id]);
        Review::factory()->count(3)->create(['listing_id' => $listing->id]);

        $response = $this->getJson("/api/v1/listings/{$listing->id}/reviews");

        $response->assertOk();
        $response->assertJsonStructure(['data']);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_authenticated_user_can_submit_review(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $customer = User::factory()->create(['role' => 'customer']);
        $listing = Listing::factory()->create(['provider_id' => $provider->id]);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/listings/{$listing->id}/reviews", [
                'rating'  => 4.5,
                'comment' => 'Great experience!',
            ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['rating' => '4.5']);
        $this->assertDatabaseHas('reviews', [
            'listing_id'  => $listing->id,
            'reviewer_id' => $customer->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_submit_review(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $listing = Listing::factory()->create(['provider_id' => $provider->id]);

        $response = $this->postJson("/api/v1/listings/{$listing->id}/reviews", [
            'rating' => 5,
        ]);

        $response->assertStatus(401);
    }

    public function test_review_rating_must_be_between_1_and_5(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $customer = User::factory()->create(['role' => 'customer']);
        $listing = Listing::factory()->create(['provider_id' => $provider->id]);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/listings/{$listing->id}/reviews", [
                'rating' => 6,
            ]);

        $response->assertStatus(422);
    }

    public function test_review_rating_is_required(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $customer = User::factory()->create(['role' => 'customer']);
        $listing = Listing::factory()->create(['provider_id' => $provider->id]);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/listings/{$listing->id}/reviews", [
                'comment' => 'Great place',
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['rating']]);
    }

    public function test_user_can_update_own_review(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $customer = User::factory()->create(['role' => 'customer']);
        $listing = Listing::factory()->create(['provider_id' => $provider->id]);

        // Submit initial review
        $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/listings/{$listing->id}/reviews", [
                'rating'  => 3,
                'comment' => 'Okay',
            ]);

        // Update review (same user, same listing -> upsert)
        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/listings/{$listing->id}/reviews", [
                'rating'  => 5,
                'comment' => 'Amazing!',
            ]);

        $response->assertStatus(201);
        $this->assertEquals(1, Review::where('listing_id', $listing->id)->count());
        $this->assertDatabaseHas('reviews', ['listing_id' => $listing->id, 'comment' => 'Amazing!']);
    }

    public function test_review_includes_reviewer_info(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $listing = Listing::factory()->create(['provider_id' => $provider->id]);
        Review::factory()->create(['listing_id' => $listing->id]);

        $response = $this->getJson("/api/v1/listings/{$listing->id}/reviews");

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['id', 'rating', 'reviewer']]]);
    }

    public function test_empty_listing_returns_no_reviews(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $listing = Listing::factory()->create(['provider_id' => $provider->id]);

        $response = $this->getJson("/api/v1/listings/{$listing->id}/reviews");

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }
}
