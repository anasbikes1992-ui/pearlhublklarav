<?php

namespace App\Services;

use App\Models\Escrow;
use RuntimeException;

class EscrowService
{
    public function __construct(
        private readonly VerticalPolicy $verticalPolicy,
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function release(Escrow $escrow, ?array $meta = null): Escrow
    {
        $vertical = (string) optional($escrow->booking?->listing)->vertical;
        if ($vertical !== '' && ! $this->verticalPolicy->requiresEscrow($vertical)) {
            throw new RuntimeException('Escrow release is not allowed for this vertical.');
        }

        $escrow->status = 'released';
        $escrow->released_at = now();

        if ($meta !== null) {
            $escrow->meta = array_merge($escrow->meta ?? [], $meta);
        }

        $escrow->save();

        $this->auditLogService->log(
            optional($escrow->booking)->customer_id,
            'escrow.released',
            Escrow::class,
            $escrow->id,
            ['meta' => $meta ?? []]
        );

        return $escrow->refresh();
    }

    public function cancel(Escrow $escrow, ?array $meta = null): Escrow
    {
        $escrow->status = 'cancelled';

        if ($meta !== null) {
            $escrow->meta = array_merge($escrow->meta ?? [], $meta);
        }

        $escrow->save();

        $this->auditLogService->log(
            optional($escrow->booking)->customer_id,
            'escrow.cancelled',
            Escrow::class,
            $escrow->id,
            ['meta' => $meta ?? []]
        );

        return $escrow->refresh();
    }
}
