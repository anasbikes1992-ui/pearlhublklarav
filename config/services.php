<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'webxpay' => [
        'merchant_id' => env('WEBXPAY_MERCHANT_ID'),
        'webhook_secret' => env('WEBXPAY_WEBHOOK_SECRET'),
        'api_username' => env('WEBXPAY_API_USERNAME'),
        'api_password' => env('WEBXPAY_API_PASSWORD'),
        'secret_key' => env('WEBXPAY_SECRET_KEY'),
        'public_key' => env('WEBXPAY_PUBLIC_KEY'),
        'enc_method' => env('WEBXPAY_ENC_METHOD', 'JCs3J+6oSz4V0LgE0zi/Bg=='),
        'checkout_url' => env('WEBXPAY_CHECKOUT_URL', 'https://webxpay.com/index.php?route=checkout/billing'),
    ],

    'genie' => [
        'merchant_id' => env('GENIE_MERCHANT_ID'),
        'webhook_secret' => env('GENIE_WEBHOOK_SECRET'),
    ],

    'koko_pay' => [
        'merchant_id' => env('KOKO_PAY_MERCHANT_ID'),
        'webhook_secret' => env('KOKO_PAY_WEBHOOK_SECRET'),
    ],

    'mint_pay' => [
        'merchant_id' => env('MINT_PAY_MERCHANT_ID'),
        'webhook_secret' => env('MINT_PAY_WEBHOOK_SECRET'),
    ],

    'translation' => [
        'provider' => env('TRANSLATION_PROVIDER', 'libretranslate'),
        'base_url' => env('LIBRETRANSLATE_URL', 'https://libretranslate.com/translate'),
        'api_key' => env('LIBRETRANSLATE_API_KEY'),
        'google_api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
    ],

    'xai' => [
        'key' => env('XAI_API_KEY'),
        'model' => env('XAI_MODEL', 'grok-2-latest'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'voice' => [
        'provider' => env('VOICE_TRANSCRIBE_PROVIDER', 'deepgram'),
    ],

    'deepgram' => [
        'key' => env('DEEPGRAM_API_KEY'),
    ],

    'reverb' => [
        'app_id' => env('REVERB_APP_ID'),
        'app_key' => env('REVERB_APP_KEY'),
        'app_secret' => env('REVERB_APP_SECRET'),
        'host' => env('REVERB_HOST', '127.0.0.1'),
        'port' => env('REVERB_PORT', 8080),
        'scheme' => env('REVERB_SCHEME', 'http'),
    ],

];
