<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends BaseApiController
{
    public function __construct(private readonly SearchService $searchService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'vertical' => ['nullable', 'string', 'in:property,stay,vehicle,event,sme,experience,taxi'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = min((int) ($validated['per_page'] ?? 20), 100);

        $results = $this->searchService->search(
            query: $validated['q'] ?? '',
            filters: array_filter([
                'vertical' => $validated['vertical'] ?? null,
            ]),
            perPage: $perPage,
        );

        return $this->success($results);
    }
}
