<?php

namespace App\Services\Payments;

use Illuminate\Support\Str;

class WebXPayGateway implements PaymentGatewayInterface
{
    public function createCheckout(array $payload): array
    {
        return [
            'provider' => 'webxpay',
            'status' => 'initiated',
            'reference' => $payload['reference'] ?? null,
            'meta' => $payload,
        ];
    }

    public function verifyWebhook(array $payload, array $context = []): array
    {
        $secret = (string) config('services.webxpay.webhook_secret');
        $headerSignature = (string) ($context['signature'] ?? '');
        $rawBody = (string) ($context['raw_body'] ?? json_encode($payload));

        $computed = hash_hmac('sha256', $rawBody, $secret);
        $verified = $secret !== '' && $headerSignature !== '' && hash_equals($computed, $headerSignature);

        return [
            'provider' => 'webxpay',
            'verified' => $verified,
            'event_id' => (string) ($payload['order_id'] ?? $payload['transaction_id'] ?? Str::uuid()),
            'transaction_reference' => $payload['transaction_id'] ?? $payload['order_id'] ?? null,
            'amount' => isset($payload['amount']) ? (float) $payload['amount'] : null,
            'currency' => $payload['currency'] ?? 'LKR',
            'status' => $payload['status'] ?? 'unknown',
            'payload' => $payload,
        ];
    }
}
