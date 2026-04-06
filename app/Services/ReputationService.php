<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Review;
use App\Models\User;
use App\Models\Listing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Reputation Service - Manages user and listing reputation scores
 * based on reviews, bookings, and platform behavior
 */
class ReputationService
{
    private AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Calculate provider reputation score
     */
    public function calculateProviderScore(User $provider): float
    {
        $cacheKey = "provider_reputation:{$provider->id}";

        return Cache::remember($cacheKey, 3600, function () use ($provider) {
            // Get all listings by provider
            $listingIds = $provider->listings()->pluck('id');

            if ($listingIds->isEmpty()) {
                return 5.0; // Default score for new providers
            }

            // Review metrics
            $reviewStats = Review::query()
                ->whereIn('listing_id', $listingIds)
                ->selectRaw('
                    AVG(rating) as avg_rating,
                    COUNT(*) as total_reviews,
                    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_reviews,
                    SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as negative_reviews
                ')
                ->first();

            // Booking metrics
            $bookingStats = DB::table('bookings')
                ->whereIn('listing_id', $listingIds)
                ->selectRaw('
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_bookings
                ', ['completed', 'cancelled'])
                ->first();

            // Calculate components
            $ratingScore = min(($reviewStats->avg_rating ?? 3) / 5, 1) * 40; // 40% weight
            $reviewVolumeScore = min(($reviewStats->total_reviews ?? 0) / 10, 1) * 15; // 15% weight
            $positiveRatio = $reviewStats->total_reviews > 0
                ? ($reviewStats->positive_reviews / $reviewStats->total_reviews)
                : 0.5;
            $positiveScore = $positiveRatio * 20; // 20% weight

            $completionRate = $bookingStats->total_bookings > 0
                ? ($bookingStats->completed_bookings / $bookingStats->total_bookings)
                : 1;
            $reliabilityScore = $completionRate * 15; // 15% weight

            $cancellationPenalty = $bookingStats->total_bookings > 0
                ? ($bookingStats->cancelled_bookings / $bookingStats->total_bookings) * 10
                : 0;

            $totalScore = $ratingScore + $reviewVolumeScore + $positiveScore + $reliabilityScore - $cancellationPenalty;

            return max(1, min(5, round(($totalScore / 20) + 1, 2)));
        });
    }

    /**
     * Calculate listing reputation score
     */
    public function calculateListingScore(Listing $listing): float
    {
        $cacheKey = "listing_reputation:{$listing->id}";

        return Cache::remember($cacheKey, 3600, function () use ($listing) {
            $reviewStats = $listing->reviews()
                ->selectRaw('
                    AVG(rating) as avg_rating,
                    COUNT(*) as total_reviews,
                    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_reviews
                ')
                ->first();

            $bookingCount = $listing->bookings()->count();
            $completionCount = $listing->bookings()->where('status', 'completed')->count();

            $ratingScore = min(($reviewStats->avg_rating ?? 3) / 5, 1) * 50;
            $popularityScore = min($bookingCount / 5, 1) * 20;
            $reliabilityScore = $bookingCount > 0
                ? ($completionCount / $bookingCount) * 30
                : 15;

            $totalScore = $ratingScore + $popularityScore + $reliabilityScore;

            return max(1, min(5, round(($totalScore / 20) + 1, 2)));
        });
    }

    /**
     * Calculate customer reputation score
     */
    public function calculateCustomerScore(User $customer): float
    {
        $cacheKey = "customer_reputation:{$customer->id}";

        return Cache::remember($cacheKey, 3600, function () use ($customer) {
            $bookingStats = DB::table('bookings')
                ->where('customer_id', $customer->id)
                ->selectRaw('
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_bookings,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as no_shows
                ', ['completed', 'cancelled', 'no_show'])
                ->first();

            if ($bookingStats->total_bookings === 0) {
                return 3.0;
            }

            $completionRate = $bookingStats->completed_bookings / $bookingStats->total_bookings;
            $cancellationRate = $bookingStats->cancelled_bookings / $bookingStats->total_bookings;
            $noShowRate = $bookingStats->no_shows / $bookingStats->total_bookings;

            $completionScore = $completionRate * 50;
            $cancellationPenalty = $cancellationRate * 20;
            $noShowPenalty = $noShowRate * 30;

            $totalScore = $completionScore - $cancellationPenalty - $noShowPenalty;

            return max(1, min(5, round(($totalScore / 20) + 1, 2)));
        });
    }

    /**
     * Update reputation after review
     */
    public function handleNewReview(Review $review): void
    {
        $listing = $review->listing;
        $provider = $listing->provider;

        // Clear caches
        Cache::forget("listing_reputation:{$listing->id}");
        Cache::forget("provider_reputation:{$provider->id}");

        // Recalculate scores
        $listingScore = $this->calculateListingScore($listing);
        $providerScore = $this->calculateProviderScore($provider);

        // Update listing
        $listing->update(['reputation_score' => $listingScore]);

        // Log reputation change
        $this->auditLogService->log(
            $review->user_id,
            'reputation.updated',
            Review::class,
            $review->id,
            [
                'listing_score' => $listingScore,
                'provider_score' => $providerScore,
                'review_rating' => $review->rating,
            ]
        );
    }

    /**
     * Get reputation badge for score
     */
    public function getBadge(float $score): string
    {
        return match (true) {
            $score >= 4.5 => 'excellent',
            $score >= 4.0 => 'very_good',
            $score >= 3.5 => 'good',
            $score >= 3.0 => 'average',
            $score >= 2.0 => 'below_average',
            default => 'poor',
        };
    }

    /**
     * Get badge label
     */
    public function getBadgeLabel(string $badge): string
    {
        return match ($badge) {
            'excellent' => 'Excellent',
            'very_good' => 'Very Good',
            'good' => 'Good',
            'average' => 'Average',
            'below_average' => 'Below Average',
            'poor' => 'Needs Improvement',
            default => 'Unknown',
        };
    }

    /**
     * Clear reputation cache
     */
    public function clearCache(User $user): void
    {
        Cache::forget("provider_reputation:{$user->id}");
        Cache::forget("customer_reputation:{$user->id}");

        foreach ($user->listings as $listing) {
            Cache::forget("listing_reputation:{$listing->id}");
        }
    }

    /**
     * Get reputation statistics for admin dashboard
     */
    public function getStats(): array
    {
        return [
            'average_provider_score' => round(
                DB::table('users')
                    ->where('role', 'provider')
                    ->avg('reputation_score') ?? 0,
                2
            ),
            'average_listing_score' => round(
                DB::table('listings')
                    ->avg('reputation_score') ?? 0,
                2
            ),
            'top_providers' => User::query()
                ->where('role', 'provider')
                ->orderByDesc('reputation_score')
                ->limit(10)
                ->select(['id', 'full_name', 'reputation_score'])
                ->get(),
            'providers_needing_attention' => User::query()
                ->where('role', 'provider')
                ->where('reputation_score', '<', 3)
                ->count(),
        ];
    }
}
