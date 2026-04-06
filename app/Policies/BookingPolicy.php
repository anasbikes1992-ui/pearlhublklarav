<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $booking->customer_id
            || $user->id === $booking->listing->provider_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [
            UserRole::CUSTOMER->value,
            UserRole::ADMIN->value,
        ], true);
    }

    public function update(User $user, Booking $booking): bool
    {
        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        // Provider can update their own listing's bookings
        if ($user->id === $booking->listing->provider_id) {
            return true;
        }

        // Customer can update their own pending bookings
        if ($user->id === $booking->customer_id && $booking->status->canCancel()) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        // Customer can cancel their own bookings
        if ($user->id === $booking->customer_id) {
            return $booking->status->canCancel();
        }

        // Provider can cancel bookings for their listings
        if ($user->id === $booking->listing->provider_id) {
            return $booking->status->canCancel();
        }

        return false;
    }

    public function confirm(User $user, Booking $booking): bool
    {
        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        // Only provider can confirm bookings for their listings
        return $user->id === $booking->listing->provider_id;
    }

    public function refund(User $user, Booking $booking): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $booking->listing->provider_id;
    }
}
