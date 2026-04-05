<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Earning;
use App\Models\TaxiRide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxiDriverController extends BaseApiController
{
    public function earnings(Request $request): JsonResponse
    {
        $driverId = $request->user()->id;

        $earnings = Earning::query()
            ->where('user_id', $driverId)
            ->where('source', 'taxi_ride')
            ->sum('amount');

        $completedRides = TaxiRide::query()
            ->where('driver_id', $driverId)
            ->where('status', 'completed')
            ->count();

        return $this->success([
            'total_earnings' => $earnings,
            'completed_rides' => $completedRides,
            'average_per_ride' => $completedRides > 0 ? round($earnings / $completedRides, 2) : 0,
        ]);
    }

    public function acceptRide(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ride_id' => ['required', 'uuid', 'exists:taxi_rides,id'],
        ]);

        $ride = TaxiRide::findOrFail($validated['ride_id']);
        $ride->update([
            'driver_id' => $request->user()->id,
            'status' => 'accepted',
        ]);

        return $this->success($ride, 'Ride accepted', 200);
    }

    public function completeRide(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ride_id' => ['required', 'uuid', 'exists:taxi_rides,id'],
            'final_fare' => ['required', 'numeric', 'min:0'],
        ]);

        $ride = TaxiRide::findOrFail($validated['ride_id']);
        $ride->update([
            'status' => 'completed',
            'final_fare' => $validated['final_fare'],
        ]);

        // Record earning
        Earning::create([
            'user_id' => $request->user()->id,
            'amount' => $validated['final_fare'],
            'currency' => 'LKR',
            'source' => 'taxi_ride',
            'reference_type' => 'taxi_ride',
            'reference_id' => $ride->id,
            'status' => 'completed',
        ]);

        return $this->success($ride, 'Ride completed', 200);
    }
}
