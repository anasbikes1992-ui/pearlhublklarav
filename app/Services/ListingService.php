<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingType;
use App\Repositories\Contracts\ListingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;

class ListingService
{
    public function __construct(
        private readonly ListingRepositoryInterface $listingRepository,
        private readonly VerticalPolicy $verticalPolicy,
        private readonly TranslationService $translationService
    ) {
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
        $verticalRule = $this->verticalPolicy->forVertical((string) ($data['vertical'] ?? ''));
        $data['status'] = $data['status'] ?? 'pending_verification';
        $data['is_hidden'] = true;
        $data['availability_calendar'] = $data['availability_calendar'] ?? [];

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

        if (empty($data['seo_slug'])) {
            $baseSeoSlug = Str::slug($data['title'] ?? '').'-'.Str::lower((string) ($data['vertical'] ?? 'listing'));
            $seoSlug = $baseSeoSlug;
            $i = 1;

            while (Listing::query()->where('seo_slug', $seoSlug)->exists()) {
                $seoSlug = "{$baseSeoSlug}-{$i}";
                $i++;
            }

            $data['seo_slug'] = $seoSlug;
        }

        $title = (string) ($data['title'] ?? '');
        $description = (string) ($data['description'] ?? '');
        $metadata = $data['metadata'] ?? [];
        if (! is_array($metadata)) {
            throw new RuntimeException('Listing metadata must be an array.');
        }

        $metadata['translations'] = [
            'title' => $this->translationService->translateAll($title),
            'description' => $this->translationService->translateAll($description),
        ];
        $metadata['policy'] = $verticalRule;
        $data['metadata'] = $metadata;

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

        if (isset($data['title']) && ! isset($data['seo_slug'])) {
            $baseSeoSlug = Str::slug((string) $data['title']).'-'.Str::lower((string) $listing->vertical);
            $seoSlug = $baseSeoSlug;
            $i = 1;
            while (Listing::query()->where('seo_slug', $seoSlug)->where('id', '!=', $listing->id)->exists()) {
                $seoSlug = "{$baseSeoSlug}-{$i}";
                $i++;
            }
            $data['seo_slug'] = $seoSlug;
        }

        if (isset($data['title']) || isset($data['description'])) {
            $baseMetadata = is_array($listing->metadata) ? $listing->metadata : [];
            $baseMetadata['translations'] = [
                'title' => $this->translationService->translateAll((string) ($data['title'] ?? $listing->title)),
                'description' => $this->translationService->translateAll((string) ($data['description'] ?? $listing->description)),
            ];
            $data['metadata'] = $baseMetadata;
        }

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
