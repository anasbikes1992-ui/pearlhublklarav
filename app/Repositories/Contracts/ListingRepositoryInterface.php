<?php

namespace App\Repositories\Contracts;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;

interface ListingRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     */
    public function all(array $filters = []): Collection;

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
