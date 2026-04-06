<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class WalletPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function view(User $user, Wallet $wallet): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $wallet->user_id;
    }

    public function viewTransactions(User $user, Wallet $wallet): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $wallet->user_id;
    }

    public function credit(User $user, Wallet $wallet): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function debit(User $user, Wallet $wallet): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function withdraw(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id
            || $user->role === UserRole::ADMIN->value;
    }
}

class WalletTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function view(User $user, WalletTransaction $transaction): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $transaction->wallet->user_id;
    }
}
