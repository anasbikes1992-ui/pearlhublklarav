<?php

namespace App\Repositories\Eloquent;

use App\Models\Listing;
use App\Repositories\Contracts\ListingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ListingRepository implements ListingRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     */
    private function buildQuery(array $filters = []): Builder
    {
        $query = Listing::query()->with(['listingType', 'provider:id,full_name,email']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['vertical'])) {
            $query->where('vertical', $filters['vertical']);
        }

        if (! empty($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->latest();
    }

    public function all(array $filters = []): Collection
    {
        return $this->buildQuery($filters)->get();
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->buildQuery($filters)->paginate($perPage);
    }

    public function create(array $attributes): Listing
    {
        return Listing::create($attributes);
    }

    public function findOrFail(string $id): Listing
    {
        return Listing::query()->with(['listingType', 'provider'])->findOrFail($id);
    }

    public function update(Listing $listing, array $attributes): Listing
    {
        $listing->fill($attributes);
        $listing->save();

        return $listing->refresh();
    }

    public function delete(Listing $listing): bool
    {
        return (bool) $listing->delete();
    }
}
