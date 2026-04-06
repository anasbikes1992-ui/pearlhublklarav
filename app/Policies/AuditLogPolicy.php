<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function create(User $user): bool
    {
        return false; // Audit logs created by system only
    }

    public function update(User $user, AuditLog $auditLog): bool
    {
        return false; // Never update audit logs
    }

    public function delete(User $user, AuditLog $auditLog): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    public function export(User $user): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }
}
