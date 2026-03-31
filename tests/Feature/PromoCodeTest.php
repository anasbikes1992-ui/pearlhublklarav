<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_can_generate_promo_code(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);

        $response = $this->actingAs($provider, 'sanctum')
            ->postJson('/api/v1/promo-codes', [
                'type' => 'sale_confirmation',
                'code' => 'TESTPROMO1',
                'max_uses' => 5,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('promo_codes', ['code' => 'TESTPROMO1']);
    }

    public function test_can_validate_promo_code(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);

        $this->actingAs($provider, 'sanctum')
            ->postJson('/api/v1/promo-codes', [
                'type' => 'discount_fixed',
                'code' => 'DISCOUNT100',
                'value' => 100,
                'max_uses' => 10,
            ]);

        $response = $this->postJson('/api/v1/promo-codes/validate', [
            'code' => 'DISCOUNT100',
            'amount' => 5000,
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['valid' => true]);
    }

    public function test_invalid_promo_code_returns_error(): void
    {
        $response = $this->postJson('/api/v1/promo-codes/validate', [
            'code' => 'NONEXISTENT',
        ]);

        $response->assertStatus(422);
    }

    public function test_provider_can_list_own_promo_codes(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);

        $this->actingAs($provider, 'sanctum')
            ->postJson('/api/v1/promo-codes', [
                'type' => 'sale_confirmation',
                'code' => 'CODE1',
            ]);

        $response = $this->actingAs($provider, 'sanctum')
            ->getJson('/api/v1/promo-codes');

        $response->assertOk();
    }
}
