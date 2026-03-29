<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Listing;
use App\Models\VerificationAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationAuditController extends BaseApiController
{
    public function store(Request $request, Listing $listing): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected,needs_changes'],
            'notes' => ['nullable', 'string'],
            'inspected_at' => ['required', 'date'],
            'photo_urls' => ['nullable', 'array'],
            'photo_urls.*' => ['string'],
        ]);

        $audit = VerificationAudit::query()->create([
            'listing_id' => $listing->id,
            'inspector_id' => $request->user()->id,
            ...$validated,
        ]);

        if ($validated['status'] === 'approved') {
            $listing->update([
                'is_hidden' => false,
                'status' => 'published',
                'verified_at' => $validated['inspected_at'],
                'inspector_id' => $request->user()->id,
            ]);
        }

        return $this->success($audit, 'Verification audit recorded', 201);
    }
}
