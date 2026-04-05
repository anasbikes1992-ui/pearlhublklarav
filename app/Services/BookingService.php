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
            ->with(['listing.provider', 'listing.listingType', 'escrow', 'customer'])
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

        // Force Sri Lanka timezone for all date operations
        $timezone = 'Asia/Colombo';
        $startAt = !empty($payload['start_at']) 
            ? \Carbon\Carbon::parse($payload['start_at'])->setTimezone($timezone) 
            : null;
        $endAt = !empty($payload['end_at']) 
            ? \Carbon\Carbon::parse($payload['end_at'])->setTimezone($timezone) 
            : null;

        if ($startAt && $endAt) {
            // Validate: end must be after start
            if ($endAt->isBefore($startAt) || $endAt->equalTo($startAt)) {
                throw new RuntimeException('End date must be after start date.');
            }

            // Check for existing bookings by this user (prevent double booking)
            $existingUserBooking = Booking::query()
                ->where('listing_id', $listing->id)
                ->where('customer_id', $userId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->when($startAt && $endAt, function ($query) use ($startAt, $endAt): void {
                    $query->where(function ($q) use ($startAt, $endAt): void {
                        $q->whereBetween('start_at', [$startAt, $endAt])
                          ->orWhereBetween('end_at', [$startAt, $endAt])
                          ->orWhere(function ($inner) use ($startAt, $endAt): void {
                              $inner->where('start_at', '<=', $startAt)
                                    ->where('end_at', '>=', $endAt);
                          });
                    });
                })
                ->exists();

            if ($existingUserBooking) {
                throw new RuntimeException('You already have a booking for this time period.');
            }

            // Get buffer time from vertical policy
            $bufferHours = $this->verticalPolicy->forVertical($listing->vertical)['buffer_hours'] ?? 0;
            $bufferStart = $startAt->copy()->subHours($bufferHours);
            $bufferEnd = $endAt->copy()->addHours($bufferHours);

            // Check for conflicts with buffer time
            $hasConflict = Booking::query()
                ->where('listing_id', $listing->id)
                ->where('customer_id', '!=', $userId) // Other customers only
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function ($query) use ($bufferStart, $bufferEnd, $bufferHours): void {
                    // Check for any overlap including buffer
                    $query->where(function ($q) use ($bufferStart, $bufferEnd): void {
                        // Case 1: Existing booking starts within our range (with buffer)
                        $q->whereBetween('start_at', [$bufferStart, $bufferEnd])
                          // Case 2: Existing booking ends within our range (with buffer)
                          ->orWhereBetween('end_at', [$bufferStart, $bufferEnd])
                          // Case 3: Existing booking completely encompasses our range
                          ->orWhere(function ($inner) use ($bufferStart, $bufferEnd): void {
                              $inner->where('start_at', '<=', $bufferStart)
                                    ->where('end_at', '>=', $bufferEnd);
                          })
                          // Case 4: Our range completely encompasses existing booking
                          ->orWhere(function ($inner) use ($bufferStart, $bufferEnd): void {
                              $inner->where('start_at', '>=', $bufferStart)
                                    ->where('end_at', '<=', $bufferEnd);
                          });
                    });
                })
                ->exists();

            if ($hasConflict) {
                $bufferMsg = $bufferHours > 0 ? " (including {$bufferHours}h buffer)" : '';
                throw new RuntimeException("Requested date range conflicts with an existing booking{$bufferMsg}.");
            }
        }

        $basePrice = (float) $listing->price;
        $commissionRate = $this->verticalPolicy->commissionRate($listing->vertical);
        $taxRate = $this->verticalPolicy->taxRate($listing->vertical);
        $commission = $basePrice * $commissionRate;
        $tax = ($basePrice + $commission) * $taxRate;
        $total = round($basePrice + $commission + $tax, 2);

        $booking = DB::transaction(function () use ($listing, $userId, $payload, $startAt, $endAt, $basePrice, $commissionRate, $taxRate, $commission, $tax, $total): Booking {
            $booking = Booking::query()->create([
                'listing_id' => $listing->id,
                'customer_id' => $userId,
                'start_at' => $startAt,
                'end_at' => $endAt,
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
