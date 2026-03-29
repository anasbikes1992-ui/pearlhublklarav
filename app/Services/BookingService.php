<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Escrow;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;

class BookingService
{
    public function listForUser(string $userId): Collection
    {
        return Booking::query()
            ->where('customer_id', $userId)
            ->with('listing')
            ->latest()
            ->get();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createBooking(string $userId, array $payload): Booking
    {
        $listing = Listing::query()->findOrFail($payload['listing_id']);

        $basePrice = (float) $listing->price;
        $commissionRate = 0.08;
        $total = round($basePrice + ($basePrice * $commissionRate), 2);

        $booking = Booking::query()->create([
            'listing_id' => $listing->id,
            'customer_id' => $userId,
            'start_at' => $payload['start_at'] ?? null,
            'end_at' => $payload['end_at'] ?? null,
            'status' => 'pending',
            'total_amount' => $total,
            'currency' => $listing->currency,
            'payment_status' => 'pending',
            'notes' => json_encode([
                'base_price' => $basePrice,
                'commission_rate' => $commissionRate,
                'invoice_ref' => 'INV-'.strtoupper(substr((string) $listing->id, 0, 8)).'-'.now()->timestamp,
            ]),
        ]);

        Escrow::query()->create([
            'booking_id' => $booking->id,
            'amount' => $total,
            'currency' => $listing->currency,
            'status' => 'held',
            'meta' => [
                'reason' => 'booking_escrow_hold',
            ],
        ]);

        return $booking->refresh();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function updateBooking(Booking $booking, array $payload): Booking
    {
        $booking->fill($payload);
        $booking->save();

        return $booking->refresh();
    }
}
