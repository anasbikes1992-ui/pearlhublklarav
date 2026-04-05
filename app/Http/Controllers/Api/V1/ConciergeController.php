<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\AiConciergeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConciergeController extends BaseApiController
{
    public function __construct(private readonly AiConciergeService $aiConciergeService)
    {
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:3000'],
            'context' => ['nullable', 'array'],
        ]);

        $response = $this->aiConciergeService->chat(
            $request->user()?->id,
            $validated['query'],
            $validated['context'] ?? []
        );

        return $this->success($response, 'Concierge response generated');
    }
}
