<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Listing;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends BaseApiController
{
    public function index(Listing $listing): JsonResponse
    {
        $reviews = $listing->reviews()
            ->with('reviewer:id,full_name')
            ->latest()
            ->get();

        return $this->success($reviews);
    }

    public function store(Request $request, Listing $listing): JsonResponse
    {
        $validated = $request->validate([
            'rating'  => ['required', 'numeric', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = Review::query()->updateOrCreate(
            [
                'listing_id'  => $listing->id,
                'reviewer_id' => $request->user()->id,
            ],
            $validated,
        );

        return $this->success(
            $review->load('reviewer:id,full_name'),
            'Review submitted',
            201
        );
    }
}
