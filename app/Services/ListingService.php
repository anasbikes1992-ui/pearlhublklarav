<?php

namespace App\Services;

use App\Models\Listing;
use App\Repositories\Contracts\ListingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListingService
{
    public function __construct(private readonly ListingRepositoryInterface $listingRepository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function list(array $filters = []): Collection
    {
        return $this->listingRepository->all($filters);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Listing
    {
        $data['status'] = $data['status'] ?? 'pending_verification';

        return $this->listingRepository->create($data);
    }

    public function find(string $id): Listing
    {
        return $this->listingRepository->findOrFail($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): Listing
    {
        $listing = $this->listingRepository->findOrFail($id);

        return $this->listingRepository->update($listing, $data);
    }

    public function delete(string $id): bool
    {
        $listing = $this->listingRepository->findOrFail($id);

        return $this->listingRepository->delete($listing);
    }
}
