<?php

namespace App\Services;

use App\Models\TaxiRide;
use Illuminate\Database\Eloquent\Collection;

class TaxiRideService
{
    public function listForUser(string $userId): Collection
    {
        return TaxiRide::query()
            ->where('customer_id', $userId)
            ->orWhere('driver_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createRide(string $customerId, array $payload): TaxiRide
    {
        return TaxiRide::query()->create([
            'customer_id' => $customerId,
            'status' => 'searching',
            ...$payload,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function updateRide(TaxiRide $taxiRide, array $payload): TaxiRide
    {
        $taxiRide->fill($payload);

        if (($payload['status'] ?? null) === 'active' && $taxiRide->started_at === null) {
            $taxiRide->started_at = now();
        }

        if (($payload['status'] ?? null) === 'completed' && $taxiRide->completed_at === null) {
            $taxiRide->completed_at = now();
        }

        $taxiRide->save();

        return $taxiRide->refresh();
    }
}
