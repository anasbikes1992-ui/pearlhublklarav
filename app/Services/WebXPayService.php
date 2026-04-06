<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * WebXPay Service - Official redirect integration
 * 
 * Based on WebXPay documentation:
 * - Sandbox: https://stagingxpay.info/index.php?route=checkout/billing
 * - Production: https://webxpay.com/index.php?route=checkout/billing
 * - Uses RSA public key encryption for payload
 * - Async notification via webhook
 */
class WebXPayService
{
    private string $publicKey;
    private string $secretKey;
    private string $checkoutUrl;
    private bool $isSandbox;

    public function __construct()
    {
        $this->publicKey = config('webxpay.public_key', '');
        $this->secretKey = config('webxpay.secret_key', '');
        $this->isSandbox = config('webxpay.sandbox', true);
        $this->checkoutUrl = $this->isSandbox
            ? 'https://stagingxpay.info/index.php?route=checkout/billing'
            : 'https://webxpay.com/index.php?route=checkout/billing';

        if (empty($this->publicKey)) {
            throw new RuntimeException('WebXPay public key not configured');
        }
    }

    /**
     * Create a checkout session for redirect flow
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function createSession(array $params): array
    {
        $orderId = $params['order_id'] ?? $params['reference'] ?? $this->generateOrderId();
        $amount = $this->normalizeAmount($params['amount'] ?? 0);
        $currency = $params['currency'] ?? 'LKR';

        if ($amount <= 0) {
            throw new RuntimeException('Amount must be greater than zero');
        }

        // Encrypt payload
        $plainText = sprintf('%s|%s', $orderId, $this->formatAmount($amount));
        $encrypted = $this->encrypt($plainText);

        if ($encrypted === null) {
            throw new RuntimeException('Failed to encrypt payment payload');
        }

        return [
            'checkout_url' => $this->checkoutUrl,
            'payload' => [
                'first_name' => $params['first_name'] ?? '',
                'last_name' => $params['last_name'] ?? '',
                'email' => $params['email'] ?? '',
                'contact_number' => $params['contact_number'] ?? $params['phone'] ?? '',
                'address_line_one' => $params['address_line_one'] ?? '',
                'address_line_two' => $params['address_line_two'] ?? '',
                'city' => $params['city'] ?? '',
                'state' => $params['state'] ?? '',
                'postal_code' => $params['postal_code'] ?? '',
                'country' => $params['country'] ?? 'LK',
                'amount' => $this->formatAmount($amount),
                'currency' => $currency,
                'order_id' => $orderId,
                'return_url' => $params['return_url'] ?? config('webxpay.return_url', ''),
                'cancel_url' => $params['cancel_url'] ?? config('webxpay.cancel_url', ''),
                'custom_fields' => $params['custom_fields'] ?? 'cus_1|cus_2|cus_3|cus_4',
            ],
            'encrypted_data' => $encrypted,
            'gateway_reference' => $orderId,
        ];
    }

    /**
     * Verify WebXPay webhook signature
     */
    public function verifySignature(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha256', $payload, $this->secretKey);
        return hash_equals($expected, $signature);
    }

    /**
     * Handle return from WebXPay checkout
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function handleReturn(array $data): array
    {
        $orderId = $data['order_id'] ?? $data['invoice_id'] ?? null;
        $status = $data['status'] ?? 'pending';
        $transactionId = $data['transaction_id'] ?? null;

        if (empty($orderId)) {
            throw new RuntimeException('Missing order ID in return data');
        }

        // Verify with backend if needed (optional for redirect flow)
        $verified = $this->verifyPayment($orderId);

        return [
            'order_id' => $orderId,
            'status' => $this->normalizeStatus($status),
            'transaction_id' => $transactionId,
            'raw_status' => $status,
            'verified' => $verified,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? 'LKR',
            'gateway_data' => $data,
        ];
    }

    /**
     * Verify payment status with WebXPay API
     */
    public function verifyPayment(string $orderId): bool
    {
        // Implement API verification if WebXPay provides it
        // For now, rely on webhook/async notification
        return true;
    }

    /**
     * Process webhook notification
     *
     * @param array<string, mixed> $payload
     * @param string $signature
     * @return array<string, mixed>
     */
    public function processWebhook(array $payload, string $signature): array
    {
        // Verify signature
        $payloadJson = json_encode($payload);
        if (! $this->verifySignature($payloadJson, $signature)) {
            throw new RuntimeException('Invalid webhook signature');
        }

        $orderId = $payload['order_id'] ?? null;
        $status = $payload['status'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;

        if (empty($orderId) || empty($status)) {
            throw new RuntimeException('Invalid webhook payload');
        }

        return [
            'order_id' => $orderId,
            'status' => $this->normalizeStatus($status),
            'transaction_id' => $transactionId,
            'amount' => $payload['amount'] ?? null,
            'currency' => $payload['currency'] ?? 'LKR',
            'gateway_data' => $payload,
        ];
    }

    /**
     * Encrypt payload with RSA public key
     */
    private function encrypt(string $data): ?string
    {
        $key = "-----BEGIN PUBLIC KEY-----\n" . 
               chunk_split($this->publicKey, 64) .
               "-----END PUBLIC KEY-----";

        $result = openssl_public_encrypt($data, $encrypted, $key);
        
        if (! $result) {
            return null;
        }

        return base64_encode($encrypted);
    }

    /**
     * Normalize amount to float
     */
    private function normalizeAmount(float|int|string $amount): float
    {
        return (float) $amount;
    }

    /**
     * Format amount for gateway (2 decimal places)
     */
    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    /**
     * Generate unique order ID
     */
    private function generateOrderId(): string
    {
        return 'PH' . date('Ymd') . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Normalize gateway status to our status
     */
    private function normalizeStatus(string $status): string
    {
        $status = strtolower($status);

        return match ($status) {
            'success', 'completed', 'approved', 'paid' => PaymentStatus::PAID->value,
            'failed', 'declined', 'rejected', 'error' => PaymentStatus::FAILED->value,
            'cancelled', 'canceled', 'abandoned' => PaymentStatus::CANCELLED->value,
            'pending', 'processing' => PaymentStatus::PENDING->value,
            'refunded' => PaymentStatus::REFUNDED->value,
            default => PaymentStatus::PENDING->value,
        };
    }

    /**
     * Get checkout URL
     */
    public function getCheckoutUrl(): string
    {
        return $this->checkoutUrl;
    }

    /**
     * Check if sandbox mode
     */
    public function isSandbox(): bool
    {
        return $this->isSandbox;
    }
}
