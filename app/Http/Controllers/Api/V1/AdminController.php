<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends BaseApiController
{
    public function stats(): JsonResponse
    {
        return $this->success([
            'total_users'           => User::query()->count(),
            'total_listings'        => Listing::query()->where('status', 'published')->count(),
            'pending_verifications' => Listing::query()->where('status', 'pending_verification')->count(),
            'platform_revenue'      => Booking::query()
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->sum('total_amount'),
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

    public function updateUser(Request $request, string $userId): JsonResponse
    {
        $user = User::query()->findOrFail($userId);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update($validated);

        return $this->success($user, 'User updated');
    }
}
