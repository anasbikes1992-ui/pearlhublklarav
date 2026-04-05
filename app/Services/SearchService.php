<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    private const MAX_QUERY_LENGTH = 120;

    /**
     * @param array<string, mixed> $filters
     */
    public function search(string $query = '', array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->sanitizeQuery($query);

        if ($query !== '' && config('scout.driver')) {
            return Listing::search($query)
                ->query(function ($builder) use ($filters): void {
                    if (! empty($filters['vertical'])) {
                        $builder->where('vertical', $filters['vertical']);
                    }
                    $builder->where('status', 'published')->where('is_hidden', false);
                })
                ->paginate($perPage);
        }

        $builder = Listing::query()->with('listingType');

        if ($query !== '') {
            $builder->where(function ($inner) use ($query): void {
                $inner->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        if (! empty($filters['vertical'])) {
            $builder->where('vertical', $filters['vertical']);
        }

        // Always restrict to published, non-hidden listings for public search.
        $builder->where('status', 'published')->where('is_hidden', false);

        return $builder->latest()->paginate($perPage);
    }

    private function sanitizeQuery(string $query): string
    {
        $query = trim($query);
        if ($query === '') {
            return '';
        }

        // Remove control characters and collapse repeated whitespace.
        $query = preg_replace('/[[:cntrl:]]+/u', '', $query) ?? '';
        $query = preg_replace('/\s+/u', ' ', $query) ?? '';

        return mb_substr($query, 0, self::MAX_QUERY_LENGTH);
    }
}
