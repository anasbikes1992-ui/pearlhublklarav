<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingType;
use App\Repositories\Contracts\ListingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->listingRepository->paginate($filters, $perPage);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Listing
    {
        $listingType = Arr::pull($data, 'listing_type');
        $data['status'] = $data['status'] ?? 'pending_verification';
        $data['is_hidden'] = true;

        // Auto-generate a unique slug from the title if not provided.
        if (empty($data['slug'])) {
            $base = Str::slug($data['title'] ?? '');
            $slug = $base;
            $i = 1;
            while (Listing::query()->where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $data['slug'] = $slug;
        }

        $listing = $this->listingRepository->create($data);

        if (is_array($listingType)) {
            ListingType::query()->create([
                'listing_id' => $listing->id,
                ...$listingType,
            ]);
        }

        return $this->listingRepository->findOrFail($listing->id);
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
        $listingType = Arr::pull($data, 'listing_type');
        $listing = $this->listingRepository->findOrFail($id);

        if (is_array($listingType)) {
            $listing->listingType()->updateOrCreate(
                ['listing_id' => $listing->id],
                $listingType
            );
        }

        return $this->listingRepository->update($listing, $data);
    }

    public function delete(string $id): bool
    {
        $listing = $this->listingRepository->findOrFail($id);

        return $this->listingRepository->delete($listing);
    }
}
