<?php

use App\Models\Booking;
use App\Models\Listing;
use App\Models\TaxiRide;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('booking.{bookingId}', function ($user, string $bookingId): bool {
    $booking = Booking::query()->find($bookingId);
    if (! $booking) {
        return false;
    }

    return $booking->customer_id === $user->id || $booking->listing?->provider_id === $user->id;
});

Broadcast::channel('taxi.ride.{rideId}', function ($user, string $rideId): bool {
    $ride = TaxiRide::query()->find($rideId);
    if (! $ride) {
        return false;
    }

    return $ride->customer_id === $user->id || $ride->driver_id === $user->id;
});

Broadcast::channel('chat.{listingId}', function ($user, string $listingId): bool {
    $listing = Listing::query()->find($listingId);
    if (! $listing) {
        return false;
    }

    if ($listing->provider_id === $user->id) {
        return true;
    }

    $isPostConfirmation = Booking::query()
        ->where('listing_id', $listingId)
        ->where('customer_id', $user->id)
        ->whereIn('status', ['confirmed', 'completed'])
        ->exists();

    return ! $isPostConfirmation;
});
