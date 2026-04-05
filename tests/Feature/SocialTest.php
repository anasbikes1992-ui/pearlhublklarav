<?php

namespace Tests\Feature;

use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_feed_is_accessible_without_auth(): void
    {
        $response = $this->getJson('/api/v1/social/feed');

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_feed_returns_posts_in_chronological_order(): void
    {
        SocialPost::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/social/feed');

        $response->assertOk();
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_feed_can_be_filtered_by_vertical(): void
    {
        SocialPost::factory()->create(['vertical_tag' => 'property']);
        SocialPost::factory()->create(['vertical_tag' => 'stays']);

        $response = $this->getJson('/api/v1/social/feed?vertical=property');

        $response->assertOk();
        $posts = $response->json('data.data');
        foreach ($posts as $post) {
            $this->assertEquals('property', $post['vertical_tag']);
        }
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/social/posts', [
                'content'      => 'Beautiful villa in Colombo!',
                'vertical_tag' => 'property',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('social_posts', [
            'user_id'  => $user->id,
            'content'  => 'Beautiful villa in Colombo!',
        ]);
    }

    public function test_unauthenticated_user_cannot_create_post(): void
    {
        $response = $this->postJson('/api/v1/social/posts', [
            'content' => 'Test post',
        ]);

        $response->assertStatus(401);
    }

    public function test_post_content_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/social/posts', [
                'vertical_tag' => 'property',
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['content']]);
    }

    public function test_post_content_max_length_is_2000(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/social/posts', [
                'content' => str_repeat('A', 2001),
            ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_like_a_post(): void
    {
        $user = User::factory()->create();
        $post = SocialPost::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/social/posts/{$post->id}/like");

        $response->assertOk();
        $response->assertJsonFragment(['liked' => true]);
    }

    public function test_liking_a_post_increments_likes_count(): void
    {
        $user = User::factory()->create();
        $post = SocialPost::factory()->create(['likes_count' => 0]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/social/posts/{$post->id}/like");

        $this->assertDatabaseHas('social_posts', ['id' => $post->id, 'likes_count' => 1]);
    }

    public function test_liking_same_post_twice_unlikes_it(): void
    {
        $user = User::factory()->create();
        $post = SocialPost::factory()->create(['likes_count' => 0]);

        // Like
        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/social/posts/{$post->id}/like");

        // Unlike
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/social/posts/{$post->id}/like");

        $response->assertOk();
        $response->assertJsonFragment(['liked' => false]);
        $this->assertDatabaseHas('social_posts', ['id' => $post->id, 'likes_count' => 0]);
    }

    public function test_authenticated_user_can_comment_on_post(): void
    {
        $user = User::factory()->create();
        $post = SocialPost::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/social/posts/{$post->id}/comments", [
                'body' => 'Great post!',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('social_comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body'    => 'Great post!',
        ]);
    }

    public function test_comment_increments_comments_count(): void
    {
        $user = User::factory()->create();
        $post = SocialPost::factory()->create(['comments_count' => 0]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/social/posts/{$post->id}/comments", [
                'body' => 'Lovely!',
            ]);

        $this->assertDatabaseHas('social_posts', ['id' => $post->id, 'comments_count' => 1]);
    }

    public function test_public_can_list_comments_on_a_post(): void
    {
        $post = SocialPost::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/social/posts/{$post->id}/comments", ['body' => 'Hello']);

        $response = $this->getJson("/api/v1/social/posts/{$post->id}/comments");

        $response->assertOk();
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_authenticated_user_can_follow_another_user(): void
    {
        $follower = User::factory()->create();
        $target   = User::factory()->create();

        $response = $this->actingAs($follower, 'sanctum')
            ->postJson("/api/v1/social/users/{$target->id}/follow");

        $response->assertOk();
        $this->assertDatabaseHas('social_follows', [
            'follower_id'  => $follower->id,
            'following_id' => $target->id,
        ]);
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/social/users/{$user->id}/follow");

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_unfollow(): void
    {
        $follower = User::factory()->create();
        $target   = User::factory()->create();

        // Follow first
        $this->actingAs($follower, 'sanctum')
            ->postJson("/api/v1/social/users/{$target->id}/follow");

        // Then unfollow
        $response = $this->actingAs($follower, 'sanctum')
            ->deleteJson("/api/v1/social/users/{$target->id}/follow");

        $response->assertOk();
        $this->assertDatabaseMissing('social_follows', [
            'follower_id'  => $follower->id,
            'following_id' => $target->id,
        ]);
    }

    public function test_public_can_view_user_social_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/social/users/{$user->id}/profile");

        $response->assertOk();
        $response->assertJsonStructure(['data' => [
            'id',
            'name',
            'followers_count',
            'following_count',
            'posts_count',
            'posts',
        ]]);
    }

    public function test_social_profile_shows_correct_follower_count(): void
    {
        $user      = User::factory()->create();
        $follower1 = User::factory()->create();
        $follower2 = User::factory()->create();

        $this->actingAs($follower1, 'sanctum')
            ->postJson("/api/v1/social/users/{$user->id}/follow");
        $this->actingAs($follower2, 'sanctum')
            ->postJson("/api/v1/social/users/{$user->id}/follow");

        $response = $this->getJson("/api/v1/social/users/{$user->id}/profile");

        $response->assertOk();
        $this->assertEquals(2, $response->json('data.followers_count'));
    }
}
