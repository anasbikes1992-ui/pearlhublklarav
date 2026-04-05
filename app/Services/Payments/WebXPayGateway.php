<?php

namespace App\Services\Payments;

use RuntimeException;
use Illuminate\Support\Str;

class WebXPayGateway implements PaymentGatewayInterface
{
    public function createCheckout(array $payload): array
    {
        $orderId = (string) ($payload['order_id'] ?? $payload['reference'] ?? Str::uuid());
        $amount = $this->normalizeAmount($payload['amount'] ?? $payload['total_amount'] ?? 0);
        $currency = (string) ($payload['currency'] ?? 'LKR');

        if ($amount <= 0) {
            throw new RuntimeException('WebXPay checkout amount must be greater than zero.');
        }

        $publicKey = (string) config('services.webxpay.public_key', '');
        if ($publicKey === '') {
            throw new RuntimeException('WebXPay public key is not configured.');
        }

        $plainText = sprintf('%s|%s', $orderId, $this->amountForGateway($amount));
        if (! openssl_public_encrypt($plainText, $encrypted, $publicKey)) {
            throw new RuntimeException('WebXPay payload encryption failed.');
        }

        $customFieldsRaw = (string) ($payload['custom_fields_raw'] ?? 'cus_1|cus_2|cus_3|cus_4');
        $checkoutUrl = (string) config('services.webxpay.checkout_url', 'https://webxpay.com/index.php?route=checkout/billing');

        $fields = [
            'first_name' => (string) ($payload['first_name'] ?? ''),
            'last_name' => (string) ($payload['last_name'] ?? ''),
            'email' => (string) ($payload['email'] ?? ''),
            'contact_number' => (string) ($payload['contact_number'] ?? ''),
            'address_line_one' => (string) ($payload['address_line_one'] ?? ''),
            'address_line_two' => (string) ($payload['address_line_two'] ?? ''),
            'city' => (string) ($payload['city'] ?? ''),
            'state' => (string) ($payload['state'] ?? ''),
            'postal_code' => (string) ($payload['postal_code'] ?? ''),
            'country' => (string) ($payload['country'] ?? 'Sri Lanka'),
            'process_currency' => $currency,
            'payment_gateway_id' => (string) ($payload['payment_gateway_id'] ?? ''),
            'multiple_payment_gateway_ids' => (string) ($payload['multiple_payment_gateway_ids'] ?? ''),
            'cms' => (string) ($payload['cms'] ?? 'Laravel'),
            'custom_fields' => base64_encode($customFieldsRaw),
            'enc_method' => (string) config('services.webxpay.enc_method', 'JCs3J+6oSz4V0LgE0zi/Bg=='),
            'secret_key' => (string) config('services.webxpay.secret_key', ''),
            'payment' => base64_encode($encrypted),
        ];

        return [
            'provider' => 'webxpay',
            'status' => 'initiated',
            'reference' => $orderId,
            'checkout_url' => $checkoutUrl,
            'method' => 'POST',
            'form_fields' => $fields,
            'meta' => [
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
                'api_username' => (string) config('services.webxpay.api_username', ''),
                'api_password' => (string) config('services.webxpay.api_password', ''),
            ],
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

    private function normalizeAmount(mixed $amount): float
    {
        if (is_numeric($amount)) {
            return round((float) $amount, 2);
        }

        return 0;
    }

    private function amountForGateway(float $amount): string
    {
        $formatted = number_format($amount, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }
}
