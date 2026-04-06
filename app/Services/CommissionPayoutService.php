<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Earning;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionPayoutService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly VerticalPolicy $verticalPolicy,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    /**
     * Calculate commission breakdown for a booking
     */
    public function calculateCommission(Booking $booking): array
    {
        $listing = $booking->listing;
        $vertical = $listing->vertical;

        $basePrice = (float) $booking->total_amount;

        // Get rates from vertical policy or database config
        $commissionRate = $this->getCommissionRate($vertical);
        $platformFeeRate = $this->getPlatformFeeRate($vertical);
        $taxRate = $this->verticalPolicy->taxRate($vertical);

        $platformCommission = round($basePrice * $commissionRate, 2);
        $platformFee = round($basePrice * $platformFeeRate, 2);
        $taxAmount = round($basePrice * $taxRate, 2);

        $providerEarnings = round($basePrice - $platformCommission - $platformFee - $taxAmount, 2);

        return [
            'base_amount' => $basePrice,
            'platform_commission' => $platformCommission,
            'platform_fee' => $platformFee,
            'tax_amount' => $taxAmount,
            'provider_earnings' => $providerEarnings,
            'commission_rate' => $commissionRate,
            'platform_fee_rate' => $platformFeeRate,
            'tax_rate' => $taxRate,
        ];
    }

    /**
     * Process payout to provider when booking is completed
     */
    public function processProviderPayout(Booking $booking): void
    {
        $auditData = DB::transaction(function () use ($booking): array {
            $lockedBooking = Booking::query()
                ->with(['listing.provider'])
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->status !== 'completed') {
                throw new \RuntimeException('Booking must be completed before payout');
            }

            if (Earning::query()->where('booking_id', $lockedBooking->id)->exists()) {
                throw new \RuntimeException('Payout already processed for this booking');
            }

            $provider = $lockedBooking->listing->provider;
            $commissionBreakdown = $this->calculateCommission($lockedBooking);

            // Create earning record
            $earning = Earning::query()->create([
                'user_id' => $provider->id,
                'booking_id' => $lockedBooking->id,
                'amount' => $commissionBreakdown['provider_earnings'],
                'currency' => $lockedBooking->currency,
                'platform_fee' => $commissionBreakdown['platform_fee'],
                'commission' => $commissionBreakdown['platform_commission'],
                'tax' => $commissionBreakdown['tax_amount'],
                'status' => 'pending',
                'payout_method' => 'wallet',
            ]);

            // Credit provider's wallet
            $this->walletService->credit(
                userId: $provider->id,
                amount: $commissionBreakdown['provider_earnings'],
                provider: 'provider_earnings',
                reference: $lockedBooking->id,
                meta: [
                    'booking_id' => $lockedBooking->id,
                    'earning_id' => $earning->id,
                    'commission_breakdown' => $commissionBreakdown,
                ]
            );

            $earning->update(['status' => 'paid', 'paid_at' => now()]);

            return [
                'provider_id' => $provider->id,
                'booking_id' => $lockedBooking->id,
                'amount' => $commissionBreakdown['provider_earnings'],
                'commission' => $commissionBreakdown['platform_commission'],
            ];
        });

        $this->auditLogService->log(
            $auditData['provider_id'],
            'payout.processed',
            Booking::class,
            $auditData['booking_id'],
            [
                'amount' => $auditData['amount'],
                'commission' => $auditData['commission'],
            ]
        );
    }

    /**
     * Get pending payouts summary for admin dashboard
     */
    public function getPendingPayoutsSummary(): array
    {
        $pendingEarnings = Earning::query()
            ->where('status', 'pending')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->first();

        $pendingByProvider = Earning::query()
            ->where('status', 'pending')
            ->with('user:id,full_name,email')
            ->selectRaw('user_id, COUNT(*) as bookings, SUM(amount) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'total_pending' => $pendingEarnings->total ?? 0,
            'count_pending' => $pendingEarnings->count ?? 0,
            'by_provider' => $pendingByProvider,
        ];
    }

    /**
     * Bulk process payouts for multiple bookings
     */
    public function bulkProcessPayouts(array $bookingIds): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($bookingIds as $bookingId) {
            try {
                $booking = Booking::query()->find($bookingId);
                if (!$booking) {
                    $results['failed'][] = ['id' => $bookingId, 'error' => 'Booking not found'];
                    continue;
                }

                $this->processProviderPayout($booking);
                $results['success'][] = $bookingId;
            } catch (\Exception $e) {
                Log::error('Payout failed for booking ' . $bookingId, ['error' => $e->getMessage()]);
                $results['failed'][] = ['id' => $bookingId, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Get commission rate from config or fallback to vertical policy
     */
    private function getCommissionRate(string $vertical): float
    {
        $config = \App\Models\VerticalFeeConfig::query()
            ->where('vertical', $vertical)
            ->where('is_active', true)
            ->first();

        if ($config && $config->commission_rate > 0) {
            return $config->commission_rate;
        }

        return $this->verticalPolicy->commissionRate($vertical);
    }

    /**
     * Get platform fee rate from config
     */
    private function getPlatformFeeRate(string $vertical): float
    {
        $config = \App\Models\VerticalFeeConfig::query()
            ->where('vertical', $vertical)
            ->where('is_active', true)
            ->first();

        if ($config) {
            return ($config->service_charge_rate ?? 0) + ($config->tourism_tax_rate ?? 0);
        }

        return 0;
    }
}
