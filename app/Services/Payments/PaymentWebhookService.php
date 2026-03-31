<?php

namespace App\Services\Payments;

use App\Models\IdempotencyKey;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentWebhookService
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     */
    public function verifyAndReserve(string $gateway, array $payload, array $context): IdempotencyKey
    {
        $verification = app(PaymentService::class)->verifyWebhook($gateway, $payload, $context);

        if (! ($verification['verified'] ?? false)) {
            throw new RuntimeException("Webhook signature validation failed for gateway [{$gateway}].");
        }

        $eventId = (string) ($verification['event_id'] ?? '');
        if ($eventId === '') {
            throw new RuntimeException('Webhook is missing event identifier.');
        }

        $idempotencyKey = "{$gateway}:{$eventId}";
        $payloadHash = hash('sha256', json_encode($payload));

        return DB::transaction(function () use ($idempotencyKey, $gateway, $payloadHash, $verification): IdempotencyKey {
            /** @var IdempotencyKey $record */
            $record = IdempotencyKey::query()->firstOrCreate(
                ['key' => $idempotencyKey],
                [
                    'gateway' => $gateway,
                    'payload_hash' => $payloadHash,
                    'status' => 'received',
                    'meta' => ['verification' => $verification],
                ]
            );

            if ($record->payload_hash !== $payloadHash) {
                throw new RuntimeException('Idempotency conflict detected with mismatched payload hash.');
            }

            return $record;
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function process(string $gateway, string $idempotencyKeyValue, array $payload): void
    {
        DB::transaction(function () use ($gateway, $idempotencyKeyValue, $payload): void {
            $record = IdempotencyKey::query()->where('key', $idempotencyKeyValue)->lockForUpdate()->firstOrFail();

            if ($record->status === 'processed') {
                return;
            }

            $userId = (string) ($payload['user_id'] ?? '');
            if ($userId === '') {
                throw new RuntimeException('Webhook payload is missing required user_id.');
            }

            $wallet = Wallet::query()->firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'currency' => (string) ($payload['currency'] ?? 'LKR'), 'status' => 'active']
            );

            Transaction::query()->updateOrCreate(
                [
                    'provider' => $gateway,
                    'external_reference' => (string) ($payload['transaction_reference'] ?? $payload['transaction_id'] ?? $idempotencyKeyValue),
                ],
                [
                    'wallet_id' => $wallet->id,
                    'amount' => (float) ($payload['amount'] ?? 0),
                    'currency' => (string) ($payload['currency'] ?? 'LKR'),
                    'status' => (string) ($payload['status'] ?? 'pending'),
                    'meta' => ['raw_payload' => $payload],
                ]
            );

            $record->update([
                'status' => 'processed',
                'processed_at' => now(),
                'meta' => array_merge($record->meta ?? [], ['processed_at' => now()->toIso8601String()]),
            ]);
        });
    }
}
