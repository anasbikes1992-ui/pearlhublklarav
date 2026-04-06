<?php

declare(strict_types=1);

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class ConfirmBookingAction
{
    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    public function execute(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            if ($booking->status !== BookingStatus::PENDING) {
                throw new \RuntimeException('Only pending bookings can be confirmed');
            }

            $booking->status = BookingStatus::CONFIRMED;
            $booking->confirmed_at = now();
            $booking->save();

            // Create escrow if required
            $vertical = $booking->listing->vertical;
            if ($vertical && $vertical->requiresEscrow()) {
                // Escrow creation handled by observer or separate action
            }

            $this->auditLogService->log(
                $booking->listing->provider_id,
                'booking.confirmed',
                Booking::class,
                $booking->id,
                [
                    'listing_id' => $booking->listing_id,
                    'customer_id' => $booking->customer_id,
                ]
            );

            return $booking;
        });
    }
}
