<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Escrow;
use App\Enums\BookingStatus;
use App\Services\EscrowService;
use App\Services\WalletService;
use App\Services\AuditLogService;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Release escrow funds to provider
 * Triggered when booking is completed or after safety period
 */
class ReleaseEscrowJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        private string $escrowId,
    ) {}

    public function handle(
        EscrowService $escrowService,
        WalletService $walletService,
        AuditLogService $auditLogService,
        WhatsAppService $whatsAppService,
    ): void {
        Log::info('ReleaseEscrowJob started', ['escrow_id' => $this->escrowId]);

        $escrow = Escrow::query()->find($this->escrowId);

        if (! $escrow) {
            Log::warning("Escrow not found: {$this->escrowId}");
            return;
        }

        if ($escrow->status !== 'held') {
            Log::info("Escrow not in held status: {$this->escrowId} - Status: {$escrow->status}");
            return;
        }

        $booking = $escrow->booking;

        if (! $booking) {
            Log::warning("Booking not found for escrow: {$this->escrowId}");
            return;
        }

        // Check if booking is eligible for escrow release
        if (! $this->isEligibleForRelease($booking)) {
            Log::info("Booking not eligible for escrow release yet: {$booking->id}");
            return;
        }

        try {
            // Release escrow
            $result = $escrowService->release($escrow, 'booking_completed');

            // Credit provider wallet
            $provider = $booking->listing->provider;
            $walletService->credit(
                $provider->id,
                $escrow->provider_amount,
                'escrow_release',
                $escrow->id,
                [
                    'booking_id' => $booking->id,
                    'gross_amount' => $escrow->total_amount,
                    'commission' => $escrow->commission_amount,
                    'tax' => $escrow->tax_amount,
                ]
            );

            // Mark booking as paid out
            $booking->update(['paid_out_at' => now()]);

            // Log action
            $auditLogService->log(
                $provider->id,
                'escrow.released',
                Escrow::class,
                $escrow->id,
                [
                    'booking_id' => $booking->id,
                    'amount' => $escrow->provider_amount,
                    'commission' => $escrow->commission_amount,
                ]
            );

            // Send notification
            $whatsAppService->sendPayoutNotification(
                $provider,
                $escrow->provider_amount,
                $escrow->id
            );

            // Dispatch payout job for actual transfer
            ProcessPayoutsJob::dispatch($provider->id, $booking->id)
                ->delay(now()->addMinutes(5));

            Log::info("Escrow released successfully: {$this->escrowId}");
        } catch (\Exception $e) {
            Log::error("Failed to release escrow: {$this->escrowId}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function isEligibleForRelease(Booking $booking): bool
    {
        // Must be completed status
        if ($booking->status !== BookingStatus::COMPLETED) {
            // Check if checkout date has passed (auto-complete eligible)
            if ($booking->end_at && $booking->end_at->isPast()) {
                // Auto-complete the booking
                $booking->update(['status' => BookingStatus::COMPLETED]);
                return true;
            }
            return false;
        }

        // Safety period after checkout (24 hours for most verticals)
        $safetyPeriod = $booking->listing->vertical?->bufferHours() ?? 24;
        $releaseTime = $booking->end_at?->addHours($safetyPeriod);

        if ($releaseTime && $releaseTime->isFuture()) {
            // Reschedule for later
            self::dispatch($booking->escrow->id)
                ->delay($releaseTime->addMinutes(5));
            return false;
        }

        return true;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ReleaseEscrowJob failed', [
            'escrow_id' => $this->escrowId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
