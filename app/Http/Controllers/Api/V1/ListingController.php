<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Models\Listing;
use App\Services\ListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingController extends BaseApiController
{
    public function __construct(private readonly ListingService $listingService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'status'      => $request->string('status')->toString(),
            'vertical'    => $request->string('vertical')->toString(),
            'provider_id' => $request->string('provider_id')->toString(),
            'search'      => $request->string('search')->toString(),
        ]);

        $perPage = min((int) $request->integer('per_page', 20), 100);
        $listings = $this->listingService->paginate($filters, $perPage);

        return $this->success($listings);
    }

    public function myListings(Request $request): JsonResponse
    {
        $listings = $this->listingService->list([
            'provider_id' => $request->user()->id,
        ]);

        return $this->success($listings);
    }

    public function store(StoreListingRequest $request): JsonResponse
    {
        $data = $request->validated();
        // Always set provider_id from the authenticated user, never from request body.
        $data['provider_id'] = $request->user()->id;
        $listing = $this->listingService->create($data);

        return $this->success($listing, 'Listing created', 201);
    }

    public function show(Listing $listing): JsonResponse
    {
        return $this->success($this->listingService->find($listing->id));
    }

    public function update(UpdateListingRequest $request, Listing $listing): JsonResponse
    {
        $updated = $this->listingService->update($listing->id, $request->validated());

        return $this->success($updated, 'Listing updated');
    }

    public function destroy(Listing $listing): JsonResponse
    {
        $this->listingService->delete($listing->id);

        return $this->success(null, 'Listing deleted');
    }
}
