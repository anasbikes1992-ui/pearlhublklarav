<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_wallet_balance(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/wallet/balance');

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['balance', 'currency', 'status']]);
    }

    public function test_wallet_balance_is_zero_for_new_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/wallet/balance');

        $response->assertOk();
        $response->assertJsonFragment(['balance' => '0.00', 'currency' => 'LKR']);
    }

    public function test_unauthenticated_user_cannot_get_wallet_balance(): void
    {
        $response = $this->getJson('/api/v1/wallet/balance');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_wallet_transactions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/wallet/transactions');

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_unauthenticated_user_cannot_list_wallet_transactions(): void
    {
        $response = $this->getJson('/api/v1/wallet/transactions');

        $response->assertStatus(401);
    }

    public function test_wallet_balance_creates_wallet_if_not_exists(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseMissing('wallets', ['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/wallet/balance');

        $this->assertDatabaseHas('wallets', ['user_id' => $user->id, 'currency' => 'LKR']);
    }

    public function test_wallet_transactions_returns_paginated_results(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/wallet/transactions');

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['data', 'total', 'per_page']]);
    }
}
