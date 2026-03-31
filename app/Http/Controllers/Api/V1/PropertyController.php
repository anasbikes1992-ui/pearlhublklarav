<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends BaseApiController
{
    public function __construct(
        private PropertyService $propertyService,
    ) {}

    public function createOwnerListing(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'owner_name' => 'required|string|max:255',
            'nic_or_company' => 'nullable|string|max:100',
            'doc_type' => 'nullable|in:deed_title,nic_copy,company_reg',
            'deed_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'metadata' => 'nullable|array',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $result = $this->propertyService->createWithOwnership(
            $request->except('deed_file'),
            $request->file('deed_file'),
            $request->user()->id,
        );

        return $this->success($result, 'Property listing created with ownership document', 201);
    }

    public function createBrokerListing(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'owner_name' => 'required|string|max:255',
            'owner_id' => 'required|uuid|exists:users,id',
            'nic_or_company' => 'nullable|string|max:100',
            'deed_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'authorization_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'metadata' => 'nullable|array',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $result = $this->propertyService->createBrokerListing(
            $request->except(['deed_file', 'authorization_file']),
            $request->file('deed_file'),
            $request->file('authorization_file'),
            $request->user()->id,
            $request->input('owner_id'),
        );

        return $this->success($result, 'Broker listing created with consent documents', 201);
    }

    public function calculateFees(Request $request): JsonResponse
    {
        $request->validate([
            'vertical' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $fees = $this->propertyService->calculateSaleFees(
            $request->input('vertical'),
            $request->input('amount'),
        );

        return $this->success($fees);
    }
}
