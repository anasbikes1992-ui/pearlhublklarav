<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
    public function profile(Request $request): JsonResponse
    {
        return $this->success($request->user()->load('profile'));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['sometimes', 'string', 'max:120'],
            'phone'     => ['sometimes', 'nullable', 'string', 'max:30'],
        ]);

        $request->user()->update($validated);

        if ($request->has('profile')) {
            $profileData = $request->validate([
                'profile.nic'            => ['sometimes', 'string', 'max:30'],
                'profile.address_line_1' => ['sometimes', 'string', 'max:200'],
                'profile.city'           => ['sometimes', 'string', 'max:80'],
                'profile.district'       => ['sometimes', 'string', 'max:80'],
                'profile.country_code'   => ['sometimes', 'string', 'size:2'],
            ]);

            $request->user()->profile()->updateOrCreate(
                ['user_id' => $request->user()->id],
                $profileData['profile'],
            );
        }

        return $this->success(
            $request->user()->fresh()->load('profile'),
            'Profile updated'
        );
    }
}
