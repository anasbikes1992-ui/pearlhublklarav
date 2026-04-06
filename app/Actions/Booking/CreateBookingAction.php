<?php

declare(strict_types=1);

namespace App\Actions\Booking;

use App\DTOs\Booking\CreateBookingDTO;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\BookingService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

class CreateBookingAction
{
    public function __construct(
        private BookingService $bookingService,
        private WalletService $walletService,
        private AuditLogService $auditLogService,
    ) {}

    public function execute(CreateBookingDTO $dto, User $customer): Booking
    {
        return DB::transaction(function () use ($dto, $customer) {
            // Check idempotency
            if ($dto->idempotencyKey) {
                $existing = Booking::query()
                    ->where('idempotency_key', $dto->idempotencyKey)
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            // Create booking
            $booking = Booking::query()->create([
                'listing_id' => $dto->listingId,
                'customer_id' => $customer->id,
                'start_at' => $dto->startAt,
                'end_at' => $dto->endAt,
                'status' => BookingStatus::PENDING->value,
                'total_amount' => $dto->totalAmount,
                'currency' => $dto->currency ?? 'LKR',
                'payment_status' => PaymentStatus::PENDING->value,
                'notes' => $dto->notes,
                'idempotency_key' => $dto->idempotencyKey,
                'guest_count' => $dto->guestCount,
                'special_requests' => $dto->specialRequests,
            ]);

            // Log action
            $this->auditLogService->log(
                $customer->id,
                'booking.created',
                Booking::class,
                $booking->id,
                [
                    'listing_id' => $dto->listingId,
                    'amount' => $dto->totalAmount,
                    'currency' => $dto->currency ?? 'LKR',
                ]
            );

            return $booking;
        });
    }
}
