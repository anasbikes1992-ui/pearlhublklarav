<?php

namespace App\Services\Payments;

use InvalidArgumentException;

class PaymentService
{
    /**
     * @param array<string, PaymentGatewayInterface> $gateways
     */
    public function __construct(private readonly array $gateways)
    {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createCheckout(string $gateway, array $payload): array
    {
        return $this->resolve($gateway)->createCheckout($payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function verifyWebhook(string $gateway, array $payload): array
    {
        return $this->resolve($gateway)->verifyWebhook($payload);
    }

    private function resolve(string $gateway): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$gateway])) {
            throw new InvalidArgumentException("Unsupported payment gateway [{$gateway}].");
        }

        return $this->gateways[$gateway];
    }
}
