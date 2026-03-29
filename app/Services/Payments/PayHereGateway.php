<?php

namespace App\Services\Payments;

class PayHereGateway implements PaymentGatewayInterface
{
    public function createCheckout(array $payload): array
    {
        return [
            'provider' => 'payhere',
            'status' => 'initiated',
            'reference' => $payload['reference'] ?? null,
            'meta' => $payload,
        ];
    }

    public function verifyWebhook(array $payload): array
    {
        return [
            'provider' => 'payhere',
            'verified' => true,
            'payload' => $payload,
        ];
    }
}
