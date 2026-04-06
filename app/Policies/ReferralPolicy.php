<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Referral;
use App\Models\User;

class ReferralPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function view(User $user, Referral $referral): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $referral->referrer_id
            || $user->id === $referral->referred_id;
    }

    public function create(User $user): bool
    {
        return false; // Referrals are created automatically
    }

    public function update(User $user, Referral $referral): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function delete(User $user, Referral $referral): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function payBonus(User $user, Referral $referral): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function viewStats(User $user): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }
}
