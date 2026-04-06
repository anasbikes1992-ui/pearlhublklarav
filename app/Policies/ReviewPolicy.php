<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Review $review): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [
            UserRole::CUSTOMER->value,
            UserRole::ADMIN->value,
        ], true);
    }

    public function update(User $user, Review $review): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $review->user_id;
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $review->user_id;
    }

    public function moderate(User $user, Review $review): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }
}
