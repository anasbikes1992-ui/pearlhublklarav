<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;

class SearchService
{
    /**
     * @param array<string, mixed> $filters
     */
    public function search(string $query = '', array $filters = []): Collection
    {
        $builder = Listing::query()->with('listingType');

        if ($query !== '') {
            $builder->where(function ($inner) use ($query): void {
                $inner->where('title', 'ilike', "%{$query}%")
                    ->orWhere('description', 'ilike', "%{$query}%");
            });
        }

        if (! empty($filters['vertical'])) {
            $builder->where('vertical', $filters['vertical']);
        }

        if (! empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        return $builder->where('is_hidden', false)->latest()->limit(100)->get();
    }
}
