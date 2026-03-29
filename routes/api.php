<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\TaxiRideController;
use App\Http\Controllers\Api\V1\VerificationAuditController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class);

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('/search', SearchController::class);

    Route::apiResource('listings', ListingController::class);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::apiResource('bookings', BookingController::class)->except(['destroy']);
        Route::apiResource('taxi-rides', TaxiRideController::class)->except(['destroy']);
        Route::post('/listings/{listing}/verify', [VerificationAuditController::class, 'store']);
    });
});
