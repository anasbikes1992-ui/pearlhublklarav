<?php

namespace App\Services\Payments;

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

    public function verifyWebhook(array $payload): array
    {
        return [
            'provider' => 'webxpay',
            'verified' => true,
            'payload' => $payload,
        ];
    }
}
