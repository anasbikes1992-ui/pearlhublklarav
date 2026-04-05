<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\PlatformSetting;
use App\Models\SocialPost;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VerticalFeeConfig;
use App\Models\WalletTransaction;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminController extends BaseApiController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function godView(): JsonResponse
    {
        return $this->success(Cache::remember('admin:god-view:v1', 300, function (): array {
            $usersByRole = User::query()
            ->groupBy('role')
            ->selectRaw('role, COUNT(*) as count')
            ->pluck('count', 'role')
            ->toArray();

            $bookingsByStatus = Booking::query()
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->pluck('count', 'status')
            ->toArray();

            $listingsByVertical = Listing::query()
            ->groupBy('vertical')
            ->selectRaw('vertical, COUNT(*) as count')
            ->pluck('count', 'vertical')
            ->toArray();

            return [
                'stats' => [
                    'total_users' => User::query()->count(),
                    'active_users' => User::query()->where('is_active', true)->count(),
                    'total_listings' => Listing::query()->count(),
                    'published_listings' => Listing::query()->where('status', 'published')->count(),
                    'total_bookings' => Booking::query()->count(),
                    'bookings_30d' => Booking::query()->where('created_at', '>=', now()->subDays(30))->count(),
                    'platform_revenue' => Booking::query()->whereNotIn('status', ['cancelled', 'refunded'])->sum('total_amount'),
                    'successful_transactions' => Transaction::query()->where('status', 'succeeded')->count(),
                    'failed_transactions' => Transaction::query()->whereIn('status', ['failed', 'cancelled'])->count(),
                    'flagged_posts' => SocialPost::query()->where('is_flagged', true)->count(),
                ],
                'breakdowns' => [
                    'users_by_role' => $usersByRole,
                    'bookings_by_status' => $bookingsByStatus,
                    'listings_by_vertical' => $listingsByVertical,
                ],
                'configs' => [
                    'vertical_fees' => VerticalFeeConfig::query()->orderBy('vertical')->get(),
                    'platform_settings' => PlatformSetting::query()->orderBy('key')->get(),
                ],
                'health' => [
                    'db_ok' => true,
                    'cache_ok' => true,
                    'queue_backlog' => DB::table('jobs')->count(),
                ],
            ];
        }));
    }

    public function stats(): JsonResponse
    {
        $bookingsByVertical = Booking::query()
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->where('created_at', '>=', now()->subDays(30))
            ->join('listings', 'bookings.listing_id', '=', 'listings.id')
            ->groupBy('listings.vertical')
            ->selectRaw('listings.vertical, COUNT(*) as count')
            ->pluck('count', 'listings.vertical')
            ->toArray();

        return $this->success([
            'total_users'              => User::query()->count(),
            'total_listings'           => Listing::query()->where('status', 'published')->count(),
            'pending_verifications'    => Listing::query()->where('status', 'pending_verification')->count(),
            'platform_revenue'         => Booking::query()
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->sum('total_amount'),
            'bookings_30d'             => Booking::query()
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'pending_kyc'              => User::query()->where('kyc_status', 'pending')->count(),
            'flagged_posts'            => SocialPost::query()->where('is_flagged', true)->count(),
            'bookings_by_vertical'     => $bookingsByVertical,
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $query = User::query()->orderByDesc('created_at');

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        $users = $query->paginate(25)->through(fn (User $u) => [
            'id'         => $u->id,
            'full_name'  => $u->full_name,
            'email'      => $u->email,
            'role'       => $u->role,
            'is_active'  => $u->is_active ?? true,
            'created_at' => $u->created_at,
        ]);

        return $this->success($users);
    }

    public function listings(Request $request): JsonResponse
    {
        $query = Listing::query()
            ->with(['provider:id,full_name,email'])
            ->orderByDesc('created_at');

        if ($request->filled('vertical')) {
            $query->where('vertical', $request->string('vertical'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $q = str_replace(['%', '_'], ['\\%', '\\_'], $request->string('q')->toString());
            $query->where(function ($inner) use ($q): void {
                $inner->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        return $this->success($query->paginate(25));
    }

    public function updateListing(Request $request, string $listingId): JsonResponse
    {
        $listing = Listing::query()->findOrFail($listingId);

        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:draft,published,pending_verification,rejected,archived'],
            'is_hidden' => ['sometimes', 'boolean'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'title' => ['sometimes', 'string', 'max:255'],
        ]);

        $listing->update($validated);
        $this->auditLogService->log(
            $request->user()?->id,
            'admin.listing.updated',
            Listing::class,
            $listing->id,
            ['changes' => $validated]
        );

        return $this->success($listing->refresh(), 'Listing updated');
    }

    public function deleteListing(Request $request, string $listingId): JsonResponse
    {
        $listing = Listing::query()->findOrFail($listingId);
        $snapshot = [
            'title' => $listing->title,
            'vertical' => $listing->vertical,
            'status' => $listing->status,
        ];
        $listing->delete();
        $this->auditLogService->log(
            $request->user()?->id,
            'admin.listing.deleted',
            Listing::class,
            $listingId,
            $snapshot
        );

        return $this->success(null, 'Listing deleted');
    }

    public function bookings(Request $request): JsonResponse
    {
        $query = Booking::query()
            ->with([
                'listing:id,title,vertical',
                'customer:id,full_name,email',
            ])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status'));
        }

        return $this->success($query->paginate(25));
    }

    public function updateBooking(Request $request, string $bookingId): JsonResponse
    {
        $booking = Booking::query()->findOrFail($bookingId);

        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:pending,confirmed,completed,cancelled,refunded'],
            'payment_status' => ['sometimes', 'string', 'in:pending,authorized,paid,failed,refunded'],
        ]);

        $booking->update($validated);
        $this->auditLogService->log(
            $request->user()?->id,
            'admin.booking.updated',
            Booking::class,
            $booking->id,
            ['changes' => $validated]
        );

        return $this->success($booking->refresh(), 'Booking updated');
    }

    public function socialPosts(Request $request): JsonResponse
    {
        $query = SocialPost::query()
            ->with(['author:id,full_name,email'])
            ->orderByDesc('created_at');

        if ($request->filled('is_flagged')) {
            $query->where('is_flagged', filter_var($request->string('is_flagged')->toString(), FILTER_VALIDATE_BOOLEAN));
        }

        return $this->success($query->paginate(25));
    }

    public function updateSocialPost(Request $request, string $postId): JsonResponse
    {
        $post = SocialPost::query()->findOrFail($postId);

        $validated = $request->validate([
            'is_flagged' => ['sometimes', 'boolean'],
            'is_pinned' => ['sometimes', 'boolean'],
            'content' => ['sometimes', 'string'],
        ]);

        $post->update($validated);
        $this->auditLogService->log(
            $request->user()?->id,
            'admin.social_post.updated',
            SocialPost::class,
            $post->id,
            ['changes' => $validated]
        );

        return $this->success($post->refresh(), 'Social post updated');
    }

    public function deleteSocialPost(Request $request, string $postId): JsonResponse
    {
        $post = SocialPost::query()->findOrFail($postId);
        $snapshot = [
            'content' => $post->content,
            'is_flagged' => $post->is_flagged,
            'is_pinned' => $post->is_pinned,
        ];
        $post->delete();
        $this->auditLogService->log(
            $request->user()?->id,
            'admin.social_post.deleted',
            SocialPost::class,
            $postId,
            $snapshot
        );

        return $this->success(null, 'Social post deleted');
    }

    public function payments(Request $request): JsonResponse
    {
        $transactions = Transaction::query()
            ->with([
                'wallet.user:id,full_name,email',
                'booking:id,status,total_amount,currency',
            ])
            ->orderByDesc('created_at')
            ->paginate(25);

        $walletTransactions = WalletTransaction::query()
            ->with(['user:id,full_name,email'])
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        return $this->success([
            'transactions' => $transactions,
            'wallet_transactions' => $walletTransactions,
            'summary' => [
                'successful_amount' => (float) Transaction::query()->where('status', 'succeeded')->sum('amount'),
                'failed_amount' => (float) Transaction::query()->whereIn('status', ['failed', 'cancelled'])->sum('amount'),
                'pending_amount' => (float) Transaction::query()->where('status', 'pending')->sum('amount'),
            ],
        ]);
    }

    public function configs(): JsonResponse
    {
        return $this->success([
            'vertical_fees' => VerticalFeeConfig::query()->orderBy('vertical')->get(),
            'platform_settings' => PlatformSetting::query()->orderBy('key')->get(),
        ]);
    }

    public function upsertVerticalFee(Request $request, string $vertical): JsonResponse
    {
        $validated = $request->validate([
            'listing_fee' => ['required', 'numeric', 'min:0'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'tourism_tax_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'service_charge_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        $config = DB::transaction(function () use ($vertical, $validated) {
            return VerticalFeeConfig::query()->updateOrCreate(
                ['vertical' => $vertical],
                $validated
            );
        });
        $this->auditLogService->log(
            $request->user()?->id,
            'admin.vertical_fee.upserted',
            VerticalFeeConfig::class,
            $config->id,
            ['vertical' => $vertical, 'changes' => $validated]
        );
        Cache::forget('admin:god-view:v1');

        return $this->success($config, 'Vertical fee config saved');
    }

    public function upsertPlatformSetting(Request $request, string $key): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'array'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $setting = DB::transaction(function () use ($key, $validated) {
            return PlatformSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $validated['value'],
                    'description' => $validated['description'] ?? null,
                ]
            );
        });
        $this->auditLogService->log(
            $request->user()?->id,
            'admin.platform_setting.upserted',
            PlatformSetting::class,
            (string) $setting->id,
            ['key' => $key, 'changes' => $validated]
        );
        Cache::forget('admin:god-view:v1');

        return $this->success($setting, 'Platform setting saved');
    }

    public function updateUser(Request $request, string $userId): JsonResponse
    {
        $user = User::query()->findOrFail($userId);
        $actorId = (string) $request->user()?->id;

        $validated = $request->validate([
            'is_active' => ['sometimes', 'boolean'],
            'role' => ['sometimes', 'string', 'in:admin,provider,customer,driver'],
        ]);

        if ($userId === $actorId && array_key_exists('role', $validated)) {
            return $this->error('Cannot change your own role', [], 403);
        }

        if ($userId === $actorId && array_key_exists('is_active', $validated) && $validated['is_active'] === false) {
            return $this->error('Cannot deactivate your own account', [], 403);
        }

        $before = ['role' => $user->role, 'is_active' => $user->is_active];

        if (array_key_exists('is_active', $validated)) {
            $user->is_active = $validated['is_active'];
        }
        if (array_key_exists('role', $validated)) {
            $user->role = $validated['role'];
        }
        $user->save();
        $this->auditLogService->log(
            $request->user()?->id,
            'admin.user.updated',
            User::class,
            $user->id,
            ['before' => $before, 'after' => ['role' => $user->role, 'is_active' => $user->is_active]]
        );
        Cache::forget('admin:god-view:v1');

        return $this->success($user, 'User updated');
    }
}
