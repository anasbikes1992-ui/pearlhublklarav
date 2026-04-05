<?php

namespace App\Services;

use App\Events\BookingStatusUpdated;
use App\Models\Booking;
use App\Models\Escrow;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookingService
{
    public function __construct(private readonly VerticalPolicy $verticalPolicy)
    {
    }

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

        if ($this->verticalPolicy->isInquiryOnly($listing->vertical)) {
            throw new RuntimeException('This vertical supports inquiry-only flow and does not allow platform bookings.');
        }

        if (! empty($payload['start_at']) && ! empty($payload['end_at'])) {
            $hasConflict = Booking::query()
                ->where('listing_id', $listing->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function ($query) use ($payload): void {
                    $query->whereBetween('start_at', [$payload['start_at'], $payload['end_at']])
                        ->orWhereBetween('end_at', [$payload['start_at'], $payload['end_at']])
                        ->orWhere(function ($inner) use ($payload): void {
                            $inner->where('start_at', '<=', $payload['start_at'])
                                ->where('end_at', '>=', $payload['end_at']);
                        });
                })
                ->exists();

            if ($hasConflict) {
                throw new RuntimeException('Requested date range conflicts with an existing booking.');
            }
        }

        $basePrice = (float) $listing->price;
        $commissionRate = $this->verticalPolicy->commissionRate($listing->vertical);
        $taxRate = $this->verticalPolicy->taxRate($listing->vertical);
        $commission = $basePrice * $commissionRate;
        $tax = ($basePrice + $commission) * $taxRate;
        $total = round($basePrice + $commission + $tax, 2);

        $booking = DB::transaction(function () use ($listing, $userId, $payload, $basePrice, $commissionRate, $taxRate, $commission, $tax, $total): Booking {
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
                    'commission_amount' => round($commission, 2),
                    'tax_rate' => $taxRate,
                    'tax_amount' => round($tax, 2),
                    'invoice_ref' => 'INV-'.strtoupper(substr((string) $listing->id, 0, 8)).'-'.now()->timestamp,
                ]),
            ]);

            if ($this->verticalPolicy->requiresEscrow($listing->vertical)) {
                Escrow::query()->create([
                    'booking_id' => $booking->id,
                    'amount' => $total,
                    'currency' => $listing->currency,
                    'status' => 'held',
                    'meta' => [
                        'reason' => 'booking_escrow_hold',
                    ],
                ]);
            }

            return $booking;
        });

        event(new BookingStatusUpdated($booking));

        return $booking->refresh();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function updateBooking(Booking $booking, array $payload): Booking
    {
        $booking->fill($payload);
        $booking->save();

        event(new BookingStatusUpdated($booking));

        return $booking->refresh();
    }
}
