<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\SmeSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SubscriptionService
{
    public function __construct(private readonly VerticalPolicy $verticalPolicy, private readonly AuditLogService $auditLogService)
    {
    }

    public function createOrRenew(string $providerId, string $plan): SmeSubscription
    {
        $planRules = $this->verticalPolicy->forSmePlan($plan);

        $subscription = DB::transaction(function () use ($providerId, $plan, $planRules): SmeSubscription {
            $existing = SmeSubscription::query()
                ->where('provider_id', $providerId)
                ->latest('expires_at')
                ->first();

            $startFrom = $existing && $existing->expires_at && $existing->expires_at->isFuture()
                ? $existing->expires_at
                : Carbon::now();

            return SmeSubscription::query()->create([
                'provider_id' => $providerId,
                'plan' => strtolower($plan),
                'expires_at' => $startFrom->copy()->addYear(),
                'product_limit' => (int) $planRules['limit'],
                'status' => 'active',
            ]);
        });

        $this->auditLogService->log($providerId, 'subscription.created', SmeSubscription::class, $subscription->id, [
            'plan' => $subscription->plan,
            'expires_at' => $subscription->expires_at?->toIso8601String(),
        ]);

        return $subscription;
    }

    public function activeForProvider(string $providerId): ?SmeSubscription
    {
        return SmeSubscription::query()
            ->where('provider_id', $providerId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();
    }

    public function ensureProductLimit(string $providerId, int $currentProductCount): void
    {
        $subscription = $this->activeForProvider($providerId);
        if (! $subscription) {
            throw new RuntimeException('Active SME subscription is required.');
        }

        if ($subscription->product_limit > 0 && $currentProductCount >= $subscription->product_limit) {
            throw new RuntimeException('Product limit reached for the current SME subscription plan.');
        }
    }

    public function suspendExpiredListings(): int
    {
        $expiredProviderIds = SmeSubscription::query()
            ->where('status', 'active')
            ->where('expires_at', '<=', now())
            ->pluck('provider_id')
            ->all();

        SmeSubscription::query()
            ->whereIn('provider_id', $expiredProviderIds)
            ->update(['status' => 'expired']);

        $updated = Listing::query()
            ->where('vertical', 'sme')
            ->whereIn('provider_id', $expiredProviderIds)
            ->update(['status' => 'paused', 'is_hidden' => true]);

        if ($updated > 0) {
            foreach ($expiredProviderIds as $providerId) {
                $this->auditLogService->log((string) $providerId, 'subscription.expired.suspend_listings', Listing::class, null, [
                    'suspended_count' => $updated,
                ]);
            }
        }

        return $updated;
    }
}
