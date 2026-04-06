<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Escrow;
use App\Models\User;

class EscrowPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function view(User $user, Escrow $escrow): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $escrow->booking->customer_id
            || $user->id === $escrow->booking->listing->provider_id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function release(User $user, Escrow $escrow): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $escrow->booking->listing->provider_id;
    }

    public function refund(User $user, Escrow $escrow): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }
}
