<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Listing $listing): bool
    {
        if ($listing->is_active) {
            return true;
        }

        return $user->role === UserRole::ADMIN->value
            || $user->id === $listing->provider_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [
            UserRole::PROVIDER->value,
            UserRole::ADMIN->value,
        ], true);
    }

    public function update(User $user, Listing $listing): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $listing->provider_id;
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $listing->provider_id;
    }

    public function restore(User $user, Listing $listing): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function forceDelete(User $user, Listing $listing): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function verify(User $user, Listing $listing): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function feature(User $user, Listing $listing): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }
}
