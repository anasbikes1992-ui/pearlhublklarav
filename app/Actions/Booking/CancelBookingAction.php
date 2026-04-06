<?php

declare(strict_types=1);

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Services\AuditLogService;
use App\Services\EscrowService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

class CancelBookingAction
{
    public function __construct(
        private WalletService $walletService,
        private EscrowService $escrowService,
        private AuditLogService $auditLogService,
    ) {}

    public function execute(Booking $booking, string $reason = ''): Booking
    {
        return DB::transaction(function () use ($booking, $reason) {
            if (! $booking->status->canCancel()) {
                throw new \RuntimeException('Booking cannot be cancelled in current status');
            }

            $previousStatus = $booking->status;

            // Update booking status
            $booking->status = BookingStatus::CANCELLED;
            $booking->cancelled_at = now();
            $booking->cancellation_reason = $reason;
            $booking->save();

            // Handle refund if paid
            if ($booking->payment_status === PaymentStatus::PAID) {
                $this->handleRefund($booking);
            }

            // Release any escrow
            if ($booking->escrow) {
                $this->escrowService->release($booking->escrow, 'booking_cancelled');
            }

            // Log action
            $this->auditLogService->log(
                $booking->customer_id,
                'booking.cancelled',
                Booking::class,
                $booking->id,
                [
                    'previous_status' => $previousStatus->value,
                    'reason' => $reason,
                    'refund_amount' => $booking->total_amount,
                ]
            );

            return $booking;
        });
    }

    private function handleRefund(Booking $booking): void
    {
        $booking->payment_status = PaymentStatus::REFUNDED;
        $booking->save();

        // Credit customer wallet with refund
        $this->walletService->credit(
            $booking->customer_id,
            $booking->total_amount,
            'refund',
            'booking_'.$booking->id,
            ['reason' => 'Booking cancelled']
        );
    }
}
