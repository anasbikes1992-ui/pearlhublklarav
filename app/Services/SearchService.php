<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    /**
     * @param array<string, mixed> $filters
     */
    public function search(string $query = '', array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
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
}
