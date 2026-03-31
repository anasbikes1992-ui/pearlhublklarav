<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CashbackRecord;
use App\Models\PlatformSetting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class CashbackService
{
    public function createPending(Booking $booking): CashbackRecord
    {
        $rate = PlatformSetting::decimal('buyer_cashback_rate', 0.005);
        $amount = (float) $booking->total_amount * $rate;

        return CashbackRecord::create([
            'booking_id' => $booking->id,
            'customer_id' => $booking->customer_id,
            'provider_id' => $booking->listing->provider_id,
            'sale_amount' => $booking->total_amount,
            'cashback_rate' => $rate,
            'cashback_amount' => round($amount, 2),
            'currency' => $booking->currency,
            'status' => 'pending',
        ]);
    }

    public function confirmByProvider(CashbackRecord $record): CashbackRecord
    {
        $record->update([
            'status' => 'approved',
            'confirmed_at' => now(),
        ]);

        return $record;
    }

    public function creditToWallet(CashbackRecord $record): CashbackRecord
    {
        return DB::transaction(function () use ($record) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $record->customer_id],
                ['balance' => 0, 'currency' => $record->currency, 'status' => 'active']
            );

            $wallet->increment('balance', $record->cashback_amount);

            WalletTransaction::create([
                'user_id' => $record->customer_id,
                'type' => 'cashback_credit',
                'amount' => $record->cashback_amount,
                'currency' => $record->currency,
                'reference_type' => CashbackRecord::class,
                'reference_id' => $record->id,
                'status' => 'completed',
                'meta' => [
                    'booking_id' => $record->booking_id,
                    'sale_amount' => $record->sale_amount,
                    'cashback_rate' => $record->cashback_rate,
                ],
            ]);

            $record->update([
                'status' => 'credited',
                'credited_at' => now(),
            ]);

            return $record->fresh();
        });
    }
}
