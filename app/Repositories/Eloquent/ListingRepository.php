<?php

namespace App\Repositories\Eloquent;

use App\Models\Listing;
use App\Repositories\Contracts\ListingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListingRepository implements ListingRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = Listing::query()->with(['listingType', 'provider']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['vertical'])) {
            $query->where('vertical', $filters['vertical']);
        }

        if (! empty($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        return $query->latest()->get();
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
