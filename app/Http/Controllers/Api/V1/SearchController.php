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
        $perPage = min((int) $request->input('per_page', 20), 100);

        $results = $this->searchService->search(
            query: $request->string('q')->toString(),
            filters: array_filter([
                'vertical' => $request->string('vertical')->toString(),
            ]),
            perPage: $perPage,
        );

        return $this->success($results);
    }
}
