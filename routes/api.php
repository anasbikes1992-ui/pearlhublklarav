<?php

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\TaxiRideController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\VerificationAuditController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class);

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::post('/payments/webhooks/webxpay', [PaymentWebhookController::class, 'webxpay']);
    Route::post('/payments/webhooks/dialog-genie', [PaymentWebhookController::class, 'dialogGenie']);

    Route::get('/search', SearchController::class);

    // Must be before apiResource to avoid {listing} catching 'my'
    Route::get('/listings/my', [ListingController::class, 'myListings'])->middleware('auth:sanctum');

    // Public listing routes (index, show)
    Route::get('/listings', [ListingController::class, 'index']);
    Route::get('/listings/{listing}', [ListingController::class, 'show']);
    Route::get('/listings/{listing}/reviews', [ReviewController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/users/profile', [UserController::class, 'profile']);
        Route::put('/users/profile', [UserController::class, 'updateProfile']);

        // Listing mutation routes (auth required)
        Route::post('/listings', [ListingController::class, 'store']);
        Route::put('/listings/{listing}', [ListingController::class, 'update']);
        Route::patch('/listings/{listing}', [ListingController::class, 'update']);
        Route::delete('/listings/{listing}', [ListingController::class, 'destroy']);

        Route::post('/listings/{listing}/reviews', [ReviewController::class, 'store']);
        Route::apiResource('bookings', BookingController::class)->except(['destroy']);
        Route::apiResource('taxi-rides', TaxiRideController::class)->except(['destroy']);
        Route::post('/listings/{listing}/verify', [VerificationAuditController::class, 'store']);

        // Admin-only routes
        Route::prefix('admin')->middleware('admin')->group(function (): void {
            Route::get('/stats', [AdminController::class, 'stats']);
            Route::get('/users', [AdminController::class, 'users']);
            Route::put('/users/{userId}', [AdminController::class, 'updateUser']);
        });
    });
});
