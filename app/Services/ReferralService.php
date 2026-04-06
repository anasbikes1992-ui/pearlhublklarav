<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    /**
     * Generate a unique referral code for a user
     */
    public function generateCode(User $user): string
    {
        $base = strtolower(Str::slug(substr($user->full_name, 0, 8)));
        $code = $base . '-' . strtoupper(Str::random(4));
        
        // Ensure uniqueness
        while (Referral::query()->where('code', $code)->exists()) {
            $code = $base . '-' . strtoupper(Str::random(4));
        }
        
        return $code;
    }

    /**
     * Create a referral record when someone signs up with a code
     */
    public function trackSignup(User $referrer, User $referee): Referral
    {
        $config = $this->getReferralConfig();
        
        $referral = Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'code' => $referrer->referral_code ?? $this->generateCode($referrer),
            'status' => Referral::STATUS_PENDING,
            'referral_type' => Referral::TYPE_SIGNUP,
            'expires_at' => now()->addDays($config['signup_expiry_days'] ?? 30),
        ]);

        // Award points immediately for signup
        if ($config['signup_points'] > 0) {
            $referral->points_awarded = $config['signup_points'];
            $referral->save();
            
            $this->awardPoints($referrer, $config['signup_points'], 'referral_signup', $referral);
        }

        $this->auditLogService->log(
            $referee->id,
            'referral.signup',
            Referral::class,
            $referral->id,
            ['referrer_id' => $referrer->id, 'points' => $config['signup_points'] ?? 0]
        );

        return $referral;
    }

    /**
     * Process referral bonus when referee makes first booking
     */
    public function processBookingBonus(Referral $referral, float $bookingAmount): void
    {
        if (!$referral->isPending() && !$referral->isQualified()) {
            return;
        }

        $config = $this->getReferralConfig();
        $bonusRate = $config['booking_bonus_rate'] ?? 0.05; // 5% default
        $maxBonus = $config['max_booking_bonus'] ?? 5000; // LKR 5000 max
        
        $bonusAmount = min($bookingAmount * $bonusRate, $maxBonus);
        
        if ($bonusAmount <= 0) {
            return;
        }

        $referral->markQualified('first_booking', $bonusAmount);
        
        // Auto-pay if enabled
        if ($config['auto_pay_bonus'] ?? false) {
            $this->payReferralBonus($referral);
        }

        $this->auditLogService->log(
            $referral->referrer_id,
            'referral.booking_bonus_qualified',
            Referral::class,
            $referral->id,
            ['bonus_amount' => $bonusAmount, 'booking_amount' => $bookingAmount]
        );
    }

    /**
     * Pay the referral bonus to referrer's wallet
     */
    public function payReferralBonus(Referral $referral): void
    {
        if (!$referral->isQualified() && !$referral->isCompleted()) {
            throw new \RuntimeException('Referral must be qualified or completed before payment');
        }

        if ($referral->revenue_bonus_amount <= 0) {
            throw new \RuntimeException('No bonus amount to pay');
        }

        DB::transaction(function () use ($referral) {
            // Credit referrer's wallet
            $this->walletService->credit(
                userId: $referral->referrer_id,
                amount: $referral->revenue_bonus_amount,
                provider: 'referral_bonus',
                reference: $referral->id,
                meta: [
                    'referral_id' => $referral->id,
                    'referred_id' => $referral->referred_id,
                    'bonus_type' => $referral->referral_type,
                ]
            );

            $referral->markPaid();
        });

        $this->auditLogService->log(
            $referral->referrer_id,
            'referral.bonus_paid',
            Referral::class,
            $referral->id,
            ['amount' => $referral->revenue_bonus_amount]
        );
    }

    /**
     * Award points to user's loyalty account
     */
    private function awardPoints(User $user, int $points, string $reason, Referral $referral): void
    {
        // Create or update PearlPoints record
        $pearlPoint = \App\Models\PearlPoint::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['total_points' => 0, 'available_points' => 0]
        );
        
        $pearlPoint->total_points += $points;
        $pearlPoint->available_points += $points;
        $pearlPoint->save();

        $this->auditLogService->log(
            $user->id,
            'points.awarded',
            \App\Models\PearlPoint::class,
            $pearlPoint->id,
            ['points' => $points, 'reason' => $reason, 'referral_id' => $referral->id]
        );
    }

    /**
     * Get referral configuration from platform settings
     */
    private function getReferralConfig(): array
    {
        $defaults = [
            'signup_points' => 100,
            'signup_expiry_days' => 30,
            'booking_bonus_rate' => 0.05,
            'max_booking_bonus' => 5000,
            'auto_pay_bonus' => false,
            'min_booking_amount' => 1000,
        ];

        // Try to get from platform settings
        $settings = \App\Models\PlatformSetting::query()
            ->where('key', 'referral_program')
            ->first();

        if ($settings && is_array($settings->value)) {
            return array_merge($defaults, $settings->value);
        }

        return $defaults;
    }

    /**
     * Get referral statistics for admin dashboard
     */
    public function getStats(): array
    {
        return [
            'total_referrals' => Referral::query()->count(),
            'pending' => Referral::query()->pending()->count(),
            'qualified' => Referral::query()->qualified()->count(),
            'completed' => Referral::query()->completed()->count(),
            'paid' => Referral::query()->paid()->count(),
            'total_points_awarded' => Referral::query()->sum('points_awarded'),
            'total_bonus_pending' => Referral::query()->unpaid()->sum('revenue_bonus_amount'),
            'total_bonus_paid' => Referral::query()->paid()->sum('revenue_bonus_amount'),
            'top_referrers' => Referral::query()
                ->selectRaw('referrer_id, COUNT(*) as count, SUM(revenue_bonus_amount) as bonus')
                ->whereIn('status', [Referral::STATUS_COMPLETED, Referral::STATUS_PAID])
                ->groupBy('referrer_id')
                ->orderByDesc('count')
                ->limit(10)
                ->with('referrer:id,full_name,email')
                ->get(),
        ];
    }
}
