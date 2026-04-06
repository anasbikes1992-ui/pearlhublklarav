<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->role === UserRole::ADMIN->value
            || $user->id === $transaction->user_id;
    }

    public function create(User $user): bool
    {
        return false; // Transactions created by system
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return false; // Never delete transactions
    }

    public function refund(User $user, Transaction $transaction): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function retry(User $user, Transaction $transaction): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }
}
