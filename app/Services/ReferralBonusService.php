<?php

namespace App\Services;

use App\Models\PearlPoint;
use App\Models\PlatformSetting;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralBonusService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function ensureReferralIdentity(User $user): void
    {
        if (! $user->referral_code) {
            $user->referral_code = $this->generateUniqueCode();
            $user->save();
        }

        PearlPoint::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['total_earned' => 0, 'total_redeemed' => 0]
        );
    }

    public function applyOnRegistration(User $user, ?string $referralCode): void
    {
        $normalized = strtoupper(trim((string) $referralCode));
        if ($normalized === '') {
            return;
        }

        $referrer = User::query()->where('referral_code', $normalized)->first();
        if (! $referrer || $referrer->id === $user->id) {
            return;
        }

        $referrerPoints = (int) PlatformSetting::decimal('referral_bonus_referrer_points', 100);
        $referredPoints = (int) PlatformSetting::decimal('referral_bonus_referred_points', 25);
        $cashBonus = (float) PlatformSetting::decimal('referral_bonus_referrer_cash_lkr', 0);

        DB::transaction(function () use ($user, $referrer, $normalized, $referrerPoints, $referredPoints, $cashBonus): void {
            $user->referred_by_user_id = $referrer->id;
            $user->save();

            $referral = Referral::query()->updateOrCreate(
                ['referred_id' => $user->id],
                [
                    'referrer_id' => $referrer->id,
                    'code' => $normalized,
                    'status' => 'credited',
                    'points_awarded' => $referrerPoints,
                    'revenue_bonus_amount' => $cashBonus,
                ]
            );

            $this->awardPoints($referrer->id, $referrerPoints);
            $this->awardPoints($user->id, $referredPoints);

            if ($cashBonus > 0) {
                $this->walletService->credit(
                    $referrer->id,
                    $cashBonus,
                    'referral_bonus',
                    'ref-'.$referral->id,
                    ['source' => 'referral_signup', 'referred_user_id' => $user->id]
                );
            }

            $this->auditLogService->log(
                $referrer->id,
                'referral.bonus.credited',
                Referral::class,
                $referral->id,
                [
                    'referrer_points' => $referrerPoints,
                    'referred_points' => $referredPoints,
                    'cash_bonus_lkr' => $cashBonus,
                    'referred_user_id' => $user->id,
                ]
            );
        });
    }

    public function grantManualBonus(string $adminId, string $userId, int $points, float $cashBonus, string $note): void
    {
        DB::transaction(function () use ($adminId, $userId, $points, $cashBonus, $note): void {
            if ($points > 0) {
                $this->awardPoints($userId, $points);
            }

            if ($cashBonus > 0) {
                $this->walletService->credit(
                    $userId,
                    $cashBonus,
                    'admin_referral_bonus',
                    'manual-'.Str::uuid()->toString(),
                    ['note' => $note]
                );
            }

            $this->auditLogService->log(
                $adminId,
                'admin.referral_bonus.granted',
                User::class,
                $userId,
                [
                    'points' => $points,
                    'cash_bonus_lkr' => $cashBonus,
                    'note' => $note,
                ]
            );
        });
    }

    private function awardPoints(string $userId, int $points): void
    {
        if ($points <= 0) {
            return;
        }

        $wallet = PearlPoint::query()->firstOrCreate(
            ['user_id' => $userId],
            ['total_earned' => 0, 'total_redeemed' => 0]
        );

        $wallet->total_earned += $points;
        $wallet->save();
    }

    private function generateUniqueCode(): string
    {
        do {
            $candidate = 'PH'.strtoupper(Str::random(8));
        } while (User::query()->where('referral_code', $candidate)->exists());

        return $candidate;
    }
}
