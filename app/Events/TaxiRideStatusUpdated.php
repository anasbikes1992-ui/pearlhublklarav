<?php

namespace App\Events;

use App\Models\TaxiRide;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaxiRideStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public TaxiRide $ride)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('taxi.ride.'.$this->ride->id)];
    }

    public function broadcastAs(): string
    {
        return 'taxi.ride.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->ride->id,
            'status' => $this->ride->status,
            'driver_id' => $this->ride->driver_id,
            'fare_estimate' => $this->ride->fare_estimate,
            'final_fare' => $this->ride->final_fare,
            'updated_at' => optional($this->ride->updated_at)->toIso8601String(),
        ];
    }
}
