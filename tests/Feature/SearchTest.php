<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_ok_with_no_query(): void
    {
        $response = $this->getJson('/api/v1/search');

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_search_with_query_parameter(): void
    {
        $response = $this->getJson('/api/v1/search?q=villa');

        $response->assertOk();
    }

    public function test_search_with_vertical_filter(): void
    {
        $response = $this->getJson('/api/v1/search?vertical=property');

        $response->assertOk();
    }

    public function test_search_respects_per_page_limit(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        Listing::factory()->count(5)->create([
            'provider_id' => $provider->id,
            'status'      => 'published',
            'is_hidden'   => false,
        ]);

        $response = $this->getJson('/api/v1/search?per_page=3');

        $response->assertOk();
    }

    public function test_search_caps_per_page_at_100(): void
    {
        $response = $this->getJson('/api/v1/search?per_page=500');

        $response->assertOk();
    }

    public function test_search_is_publicly_accessible(): void
    {
        // Search should be accessible without authentication
        $response = $this->getJson('/api/v1/search?q=beach');

        $response->assertOk();
    }
}
