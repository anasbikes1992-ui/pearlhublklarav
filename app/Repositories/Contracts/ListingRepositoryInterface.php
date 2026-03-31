<?php

namespace App\Repositories\Contracts;

use App\Models\Listing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ListingRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     */
    public function all(array $filters = []): Collection;

    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Listing;

    public function findOrFail(string $id): Listing;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(Listing $listing, array $attributes): Listing;

    public function delete(Listing $listing): bool;
}
