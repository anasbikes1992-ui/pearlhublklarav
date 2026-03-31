<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;

class HealthController extends BaseApiController
{
    public function __invoke(): JsonResponse
    {
        return $this->success([
            'service' => config('app.name'),
            'version' => 'v1',
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
