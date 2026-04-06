<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use RuntimeException;

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
            // Lock the wallet row to prevent concurrent modification
            $wallet = Wallet::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                $wallet = Wallet::query()->create([
                    'user_id' => $userId,
                    'balance' => 0,
                    'currency' => 'LKR',
                    'status' => 'active',
                ]);
            }

            $newBalance = (float) $wallet->balance + $amount;
            
            if ($newBalance < 0) {
                throw new RuntimeException('Insufficient wallet balance');
            }

            $wallet->balance = $newBalance;
            $wallet->save();

            Transaction::query()->create([
                'wallet_id' => $wallet->id,
                'provider' => $provider,
                'external_reference' => $reference,
                'amount' => $amount,
                'currency' => $wallet->currency,
                'status' => 'succeeded',
                'meta' => array_merge($meta, [
                    'balance_before' => $newBalance - $amount,
                    'balance_after' => $newBalance,
                ]),
            ]);

            return $wallet->refresh();
        });
    }

    public function debit(string $userId, float $amount, string $provider, ?string $reference = null, array $meta = []): Wallet
    {
        return $this->credit($userId, -$amount, $provider, $reference, array_merge($meta, ['type' => 'debit']));
    }

    public function computeCommission(string $vertical, float $amount): float
    {
        $rate = $this->verticalPolicy->commissionRate($vertical);

        return round($amount * $rate, 2);
    }
}
