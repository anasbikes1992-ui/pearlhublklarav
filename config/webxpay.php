<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | WebXPay Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WebXPay payment gateway integration.
    | Based on official WebXPay documentation.
    |
    */

    // Sandbox mode
    'sandbox' => env('WEBXPAY_SANDBOX', true),

    // API Credentials
    'public_key' => env('WEBXPAY_PUBLIC_KEY', ''),
    'secret_key' => env('WEBXPAY_SECRET_KEY', ''),

    // Checkout URLs
    'checkout_url' => env('WEBXPAY_CHECKOUT_URL', 'https://stagingxpay.info/index.php?route=checkout/billing'),

    // Return URLs
    'return_url' => env('WEBXPAY_RETURN_URL', config('app.url') . '/payments/success'),
    'cancel_url' => env('WEBXPAY_CANCEL_URL', config('app.url') . '/payments/cancel'),

    // Webhook
    'webhook_secret' => env('WEBXPAY_WEBHOOK_SECRET', ''),
    'webhook_url' => env('WEBXPAY_WEBHOOK_URL', config('app.url') . '/webhooks/webxpay'),

    // Legacy config (for backward compatibility)
    'merchant_id' => env('WEBXPAY_MERCHANT_ID', ''),
    'api_username' => env('WEBXPAY_API_USERNAME', ''),
    'api_password' => env('WEBXPAY_API_PASSWORD', ''),
    'enc_method' => env('WEBXPAY_ENC_METHOD', 'JCs3J+6oSz4V0LgE0zi/Bg=='),

    // Payment settings
    'default_currency' => env('WEBXPAY_CURRENCY', 'LKR'),
    'minimum_amount' => 100.00,

    // Commission settings
    'commission_rate' => 0.025, // 2.5% WebXPay fee (approximate)
];
