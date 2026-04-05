<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Services\ReferralBonusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends BaseApiController
{
    public function __construct(private readonly ReferralBonusService $referralBonusService)
    {
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name'             => ['required', 'string', 'max:120'],
            'email'                 => ['required', 'email', 'max:120', 'unique:users,email'],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'referral_code'         => ['nullable', 'string', 'max:32'],
            // Role is always 'customer' on self-registration — never trust client input.
        ]);

        $user = new User();
        $user->fill($validated);
        $user->role = User::ROLE_CUSTOMER;
        $user->save();

        $this->referralBonusService->ensureReferralIdentity($user);
        $this->referralBonusService->applyOnRegistration($user, $validated['referral_code'] ?? null);

        try {
            $token = $this->issueAccessToken($user);
        } catch (Throwable) {
            return $this->error('Authentication service is temporarily unavailable. Please try again shortly.', [], 503);
        }

        return $this->success([
            'user'  => $user,
            'token' => $token,
        ], 'Registration successful', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        /** @var User|null $user */
        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            return $this->error('Your account has been suspended. Please contact support.', [], 403);
        }

        try {
            $token = $this->issueAccessToken($user);
        } catch (Throwable) {
            return $this->error('Authentication service is temporarily unavailable. Please try again shortly.', [], 503);
        }

        return $this->success([
            'user'  => $user,
            'token' => $token,
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->success(null, 'Logged out successfully');
    }

    private function issueAccessToken(User $user): string
    {
        try {
            return $user->createToken('mobile-auth')->plainTextToken;
        } catch (Throwable $exception) {
            $this->repairPersonalAccessTokensSchema();

            return $user->createToken('mobile-auth')->plainTextToken;
        }
    }

    private function repairPersonalAccessTokensSchema(): void
    {
        if (! Schema::hasTable('personal_access_tokens') || ! Schema::hasColumn('personal_access_tokens', 'tokenable_id')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $column = DB::selectOne(
            "SELECT DATA_TYPE AS data_type
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'personal_access_tokens'
               AND COLUMN_NAME = 'tokenable_id'"
        );

        if ($column !== null && strtolower((string) $column->data_type) !== 'char') {
            DB::statement('ALTER TABLE personal_access_tokens MODIFY tokenable_id CHAR(36) NOT NULL');
        }
    }
}
