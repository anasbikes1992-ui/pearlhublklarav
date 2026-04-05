<?php

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SocialController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CashbackController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\ConciergeController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\PaymentCheckoutController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Http\Controllers\Api\V1\PromoCodeController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SmeController;
use App\Http\Controllers\Api\V1\TaxiRideController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\VerificationAuditController;
use App\Http\Controllers\Api\V1\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class);

    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::post('/payments/webhooks/webxpay', [PaymentWebhookController::class, 'webxpay']);
    Route::post('/payments/webhooks/genie', [PaymentWebhookController::class, 'genie']);
    Route::post('/payments/webhooks/koko-pay', [PaymentWebhookController::class, 'kokoPay']);
    Route::post('/payments/webhooks/mint-pay', [PaymentWebhookController::class, 'mintPay']);

    Route::get('/search', SearchController::class)->middleware('throttle:search,60,1');
    Route::post('/concierge/chat', [ConciergeController::class, 'chat'])->middleware('throttle:concierge,20,1');

    // Public promo code validation
    Route::post('/promo-codes/validate', [PromoCodeController::class, 'validate'])->middleware('throttle:promo,10,1');

    // Public fee calculator
    Route::post('/fees/calculate', [PropertyController::class, 'calculateFees'])->middleware('throttle:fees,30,1');

    // Must be before apiResource to avoid {listing} catching 'my'
    Route::get('/listings/my', [ListingController::class, 'myListings'])->middleware('auth:sanctum');
    Route::apiResource('listings', ListingController::class)->only(['index', 'show']);
    Route::apiResource('listings', ListingController::class)->except(['index', 'show'])->middleware(['auth:sanctum', 'owns.listing']);
    Route::get('/listings/{listing}/reviews', [ReviewController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/payments/checkout', [PaymentCheckoutController::class, 'create']);

        Route::get('/users/profile', [UserController::class, 'profile']);
        Route::put('/users/profile', [UserController::class, 'updateProfile']);

        Route::post('/listings/{listing}/reviews', [ReviewController::class, 'store']);
        Route::apiResource('bookings', BookingController::class)->except(['destroy']);
        Route::apiResource('taxi-rides', TaxiRideController::class)->except(['destroy']);
        Route::post('/listings/{listing}/verify', [VerificationAuditController::class, 'store']);

        // Property-specific routes (owner + broker flows)
        Route::post('/property/owner-listing', [PropertyController::class, 'createOwnerListing']);
        Route::post('/property/broker-listing', [PropertyController::class, 'createBrokerListing']);

        // Promo code management
        Route::get('/promo-codes', [PromoCodeController::class, 'index']);
        Route::post('/promo-codes', [PromoCodeController::class, 'generate']);
        Route::post('/promo-codes/redeem', [PromoCodeController::class, 'redeem']);

        // Cashback management
        Route::get('/cashback', [CashbackController::class, 'index']);
        Route::post('/cashback/{cashbackRecord}/confirm', [CashbackController::class, 'confirm']);

        // Wallet
        Route::get('/wallet/balance', [WalletController::class, 'balance']);
        Route::get('/wallet/transactions', [WalletController::class, 'transactions']);

        // Pre-booking chat
        Route::get('/chat/{listingId}/messages', [ChatController::class, 'history']);
        Route::post('/chat/messages/text', [ChatController::class, 'sendText']);
        Route::post('/chat/messages/voice', [ChatController::class, 'sendVoice']);

        // SME v2
        Route::post('/sme/subscriptions', [SmeController::class, 'subscribe']);
        Route::get('/sme/listings/{listing}/products', [SmeController::class, 'products']);
        Route::post('/sme/listings/{listing}/products', [SmeController::class, 'createProduct']);
        Route::post('/sme/sales-reports', [SmeController::class, 'reportSales']);

        // Admin-only routes
        Route::prefix('admin')->middleware('admin')->group(function (): void {
            Route::get('/stats', [AdminController::class, 'stats']);
            Route::get('/users', [AdminController::class, 'users']);
            Route::put('/users/{userId}', [AdminController::class, 'updateUser']);
            Route::post('/cashback/{cashbackRecord}/credit', [CashbackController::class, 'credit']);
        });

        // Social — auth-required write routes
        Route::prefix('social')->group(function (): void {
            Route::post('/posts', [SocialController::class, 'createPost']);
            Route::post('/posts/{post}/like', [SocialController::class, 'toggleLike']);
            Route::post('/posts/{post}/comments', [SocialController::class, 'addComment']);
            Route::post('/users/{user}/follow', [SocialController::class, 'follow']);
            Route::delete('/users/{user}/follow', [SocialController::class, 'unfollow']);
        });
    });

    // Social — public read routes (outside auth middleware)
    Route::prefix('social')->group(function (): void {
        Route::get('/feed', [SocialController::class, 'feed']);
        Route::get('/posts/{post}/comments', [SocialController::class, 'comments']);
        Route::get('/users/{user}/profile', [SocialController::class, 'profile']);
    });
});
