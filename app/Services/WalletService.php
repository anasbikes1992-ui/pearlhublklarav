<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function __construct(private readonly VerticalPolicy $verticalPolicy)
    {
    }

    public function getOrCreate(string $userId, string $currency = 'LKR'): Wallet
    {
        return Wallet::query()->firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'currency' => $currency, 'status' => 'active']
        );
    }

    public function credit(string $userId, float $amount, string $provider, ?string $reference = null, array $meta = []): Wallet
    {
        return DB::transaction(function () use ($userId, $amount, $provider, $reference, $meta): Wallet {
            $wallet = $this->getOrCreate($userId);
            $wallet->balance = (float) $wallet->balance + $amount;
            $wallet->save();

            Transaction::query()->create([
                'wallet_id' => $wallet->id,
                'provider' => $provider,
                'external_reference' => $reference,
                'amount' => $amount,
                'currency' => $wallet->currency,
                'status' => 'succeeded',
                'meta' => $meta,
            ]);

            return $wallet->refresh();
        });
    }

    public function computeCommission(string $vertical, float $amount): float
    {
        $rate = $this->verticalPolicy->commissionRate($vertical);

        return round($amount * $rate, 2);
    }
}
