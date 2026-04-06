<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Earning;
use App\Enums\BookingStatus;
use App\Services\CommissionPayoutService;
use App\Services\AuditLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Process scheduled payouts for providers
 * Run daily via scheduler
 */
class ProcessPayoutsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        private ?string $providerId = null,
        private ?string $bookingId = null,
    ) {}

    public function handle(
        CommissionPayoutService $payoutService,
        AuditLogService $auditLogService,
    ): void {
        Log::info('ProcessPayoutsJob started', [
            'provider_id' => $this->providerId,
            'booking_id' => $this->bookingId,
        ]);

        if ($this->bookingId) {
            // Process specific booking
            $this->processSingleBooking($this->bookingId, $payoutService, $auditLogService);
        } else {
            // Process all pending payouts
            $this->processBatch($payoutService, $auditLogService);
        }

        Log::info('ProcessPayoutsJob completed');
    }

    private function processSingleBooking(
        string $bookingId,
        CommissionPayoutService $payoutService,
        AuditLogService $auditLogService,
    ): void {
        $booking = Booking::query()->find($bookingId);

        if (! $booking) {
            Log::warning("Booking not found: {$bookingId}");
            return;
        }

        if (! $booking->isCompleted()) {
            Log::info("Booking not completed yet: {$bookingId}");
            return;
        }

        try {
            $result = $payoutService->processProviderPayout($booking);

            $auditLogService->log(
                $booking->listing->provider_id,
                'payout.processed',
                Booking::class,
                $booking->id,
                [
                    'amount' => $result['amount'] ?? 0,
                    'commission' => $result['commission'] ?? 0,
                    'net_amount' => $result['net_amount'] ?? 0,
                ]
            );

            Log::info("Payout processed for booking: {$bookingId}");
        } catch (\Exception $e) {
            Log::error("Failed to process payout for booking: {$bookingId}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function processBatch(
        CommissionPayoutService $payoutService,
        AuditLogService $auditLogService,
    ): void {
        $query = Booking::query()
            ->where('status', BookingStatus::COMPLETED)
            ->whereNull('paid_out_at')
            ->whereHas('escrow', function ($q) {
                $q->where('status', 'released');
            });

        if ($this->providerId) {
            $query->whereHas('listing', function ($q) {
                $q->where('provider_id', $this->providerId);
            });
        }

        $count = 0;
        $failed = 0;

        $query->chunk(100, function ($bookings) use ($payoutService, $auditLogService, &$count, &$failed) {
            foreach ($bookings as $booking) {
                try {
                    $this->processSingleBooking(
                        $booking->id,
                        $payoutService,
                        $auditLogService
                    );
                    $count++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::error("Batch payout failed for booking: {$booking->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        Log::info("Batch payout complete", [
            'processed' => $count,
            'failed' => $failed,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessPayoutsJob failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
