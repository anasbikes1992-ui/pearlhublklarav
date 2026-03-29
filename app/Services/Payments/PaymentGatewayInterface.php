<?php

namespace App\Services\Payments;

interface PaymentGatewayInterface
{
    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createCheckout(array $payload): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function verifyWebhook(array $payload, array $context = []): array;
}
