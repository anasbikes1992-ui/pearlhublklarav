<?php

namespace App\Services\Payments;

use Illuminate\Support\Str;

class DialogGenieGateway implements PaymentGatewayInterface
{
    public function createCheckout(array $payload): array
    {
        return [
            'provider' => 'dialog_genie',
            'status' => 'initiated',
            'reference' => $payload['reference'] ?? null,
            'meta' => $payload,
        ];
    }

    public function verifyWebhook(array $payload, array $context = []): array
    {
        $secret = (string) config('services.dialog_genie.webhook_secret');
        $headerSignature = (string) ($context['signature'] ?? '');
        $rawBody = (string) ($context['raw_body'] ?? json_encode($payload));

        $computed = hash_hmac('sha256', $rawBody, $secret);
        $verified = $secret !== '' && $headerSignature !== '' && hash_equals($computed, $headerSignature);

        return [
            'provider' => 'dialog_genie',
            'verified' => $verified,
            'event_id' => (string) ($payload['event_id'] ?? $payload['txn_ref'] ?? Str::uuid()),
            'transaction_reference' => $payload['txn_ref'] ?? $payload['event_id'] ?? null,
            'amount' => isset($payload['amount']) ? (float) $payload['amount'] : null,
            'currency' => $payload['currency'] ?? 'LKR',
            'status' => $payload['status'] ?? 'unknown',
            'payload' => $payload,
        ];
    }
}
