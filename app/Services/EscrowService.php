<?php

namespace App\Services;

use App\Models\Escrow;

class EscrowService
{
    public function release(Escrow $escrow, ?array $meta = null): Escrow
    {
        $escrow->status = 'released';
        $escrow->released_at = now();

        if ($meta !== null) {
            $escrow->meta = array_merge($escrow->meta ?? [], $meta);
        }

        $escrow->save();

        return $escrow->refresh();
    }

    public function cancel(Escrow $escrow, ?array $meta = null): Escrow
    {
        $escrow->status = 'cancelled';

        if ($meta !== null) {
            $escrow->meta = array_merge($escrow->meta ?? [], $meta);
        }

        $escrow->save();

        return $escrow->refresh();
    }
}
