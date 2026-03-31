<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\TaxiRide;
use App\Services\TaxiRideService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxiRideController extends BaseApiController
{
    public function __construct(private readonly TaxiRideService $taxiRideService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->taxiRideService->listForUser($request->user()->id));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pickup_latitude' => ['required', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['required', 'numeric', 'between:-180,180'],
            'dropoff_latitude' => ['required', 'numeric', 'between:-90,90'],
            'dropoff_longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return $this->success(
            $this->taxiRideService->createRide($request->user()->id, $validated),
            'Ride created',
            201
        );
    }

    public function show(TaxiRide $taxi_ride): JsonResponse
    {
        return $this->success($taxi_ride);
    }

    public function update(Request $request, TaxiRide $taxi_ride): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:searching,accepted,active,completed,cancelled'],
            'driver_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'fare_estimate' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'final_fare' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ]);

        return $this->success($this->taxiRideService->updateRide($taxi_ride, $validated), 'Ride updated');
    }
}
