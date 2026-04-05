<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    /**
     * @param array<string, mixed> $meta
     */
    public function log(?string $actorId, string $action, string $entityType, ?string $entityId = null, array $meta = []): void
    {
        AuditLog::query()->create([
            'actor_id' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'meta' => $meta,
        ]);
    }
}
