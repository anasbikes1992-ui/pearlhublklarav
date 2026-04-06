<?php

namespace App\Services;

use App\Events\BookingStatusUpdated;
use App\Models\Booking;
use App\Models\Escrow;
use App\Models\Listing;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookingService
{
    public function __construct(
        private readonly VerticalPolicy $verticalPolicy,
        private readonly AuditLogService $auditLogService,
    ) {
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
        // Check idempotency key to prevent duplicate bookings from retries
        if (!empty($payload['idempotency_key'])) {
            $existingBooking = Booking::query()
                ->where('customer_id', $userId)
                ->where('listing_id', $payload['listing_id'])
                ->where('notes->idempotency_key', $payload['idempotency_key'])
                ->first();

            if ($existingBooking) {
                return $existingBooking;
            }
        }

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
            $notes = [
                'base_price' => $basePrice,
                'commission_rate' => $commissionRate,
                'commission_amount' => round($commission, 2),
                'tax_rate' => $taxRate,
                'tax_amount' => round($tax, 2),
                'invoice_ref' => 'INV-'.strtoupper(substr((string) $listing->id, 0, 8)).'-'.now()->timestamp,
            ];

            // Store idempotency key in notes if provided
            if (!empty($payload['idempotency_key'])) {
                $notes['idempotency_key'] = $payload['idempotency_key'];
            }

            $booking = Booking::query()->create([
                'listing_id' => $listing->id,
                'customer_id' => $userId,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'status' => 'pending',
                'total_amount' => $total,
                'currency' => $listing->currency,
                'payment_status' => 'pending',
                'notes' => json_encode($notes),
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

            // Audit log
            $this->auditLogService->log($userId, 'booking.created', Booking::class, $booking->id, [
                'listing_id' => $listing->id,
                'vertical' => $listing->vertical,
                'total_amount' => $total,
                'start_at' => $startAt?->toIso8601String(),
                'end_at' => $endAt?->toIso8601String(),
            ]);

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
        $oldStatus = $booking->status;

        // Check if trying to cancel and validate against cancellation policy
        if (isset($payload['status']) && $payload['status'] === 'cancelled') {
            $this->validateCancellationPolicy($booking);
        }

        $booking->fill($payload);
        $booking->save();

        // Audit log for status changes
        if (isset($payload['status']) && $payload['status'] !== $oldStatus) {
            $this->auditLogService->log($booking->customer_id, 'booking.status_updated', Booking::class, $booking->id, [
                'old_status' => $oldStatus,
                'new_status' => $payload['status'],
                'changed_by' => auth()->id(),
            ]);
        }

        event(new BookingStatusUpdated($booking));

        return $booking->refresh();
    }

    /**
     * Validate cancellation against vertical policy
     */
    private function validateCancellationPolicy(Booking $booking): void
    {
        $listing = $booking->listing;
        $cancellationWindow = $this->verticalPolicy->getCancellationWindowHours($listing->vertical);

        if ($cancellationWindow === 0) {
            throw new RuntimeException('Cancellations are not allowed for this type of booking.');
        }

        if ($booking->start_at) {
            $hoursUntilStart = now()->diffInHours($booking->start_at, false);

            if ($hoursUntilStart < $cancellationWindow) {
                throw new RuntimeException(
                    "Cancellations must be made at least {$cancellationWindow} hours before the booking starts."
                );
            }
        }
    }
}
