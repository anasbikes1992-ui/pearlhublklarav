<?php

namespace App\Services;

use App\Models\BrokerConsent;
use App\Models\Listing;
use App\Models\OwnershipDocument;
use App\Models\VerticalFeeConfig;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PropertyService
{
    public function __construct(
        private ListingService $listingService,
    ) {}

    public function createWithOwnership(array $data, UploadedFile $deedFile, string $providerId): array
    {
        return DB::transaction(function () use ($data, $deedFile, $providerId) {
            $listing = $this->listingService->create(array_merge($data, [
                'provider_id' => $providerId,
                'vertical' => 'property',
                'status' => 'draft',
            ]));

            $path = $deedFile->store("ownership-docs/{$listing->id}", 'local');

            $document = OwnershipDocument::create([
                'listing_id' => $listing->id,
                'uploaded_by' => $providerId,
                'type' => $data['doc_type'] ?? 'deed_title',
                'file_path' => $path,
                'owner_name' => $data['owner_name'],
                'nic_or_company' => $data['nic_or_company'] ?? null,
                'status' => 'pending',
            ]);

            return ['listing' => $listing, 'document' => $document];
        });
    }

    public function createBrokerListing(array $data, UploadedFile $deedFile, UploadedFile $authFile, string $brokerId, string $ownerId): array
    {
        return DB::transaction(function () use ($data, $deedFile, $authFile, $brokerId, $ownerId) {
            $listing = $this->listingService->create(array_merge($data, [
                'provider_id' => $brokerId,
                'vertical' => 'property',
                'status' => 'draft',
                'metadata' => array_merge($data['metadata'] ?? [], ['is_broker_listing' => true]),
            ]));

            $deedPath = $deedFile->store("broker-consents/{$listing->id}", 'local');
            $authPath = $authFile->store("broker-consents/{$listing->id}", 'local');

            $consent = BrokerConsent::create([
                'listing_id' => $listing->id,
                'broker_id' => $brokerId,
                'owner_id' => $ownerId,
                'deed_file_path' => $deedPath,
                'authorization_file_path' => $authPath,
                'indemnity_accepted' => true,
                'status' => 'pending',
            ]);

            $document = OwnershipDocument::create([
                'listing_id' => $listing->id,
                'uploaded_by' => $brokerId,
                'type' => 'deed_title',
                'file_path' => $deedPath,
                'owner_name' => $data['owner_name'],
                'nic_or_company' => $data['nic_or_company'] ?? null,
                'status' => 'pending',
            ]);

            return ['listing' => $listing, 'consent' => $consent, 'document' => $document];
        });
    }

    public function calculateSaleFees(string $vertical, float $salePrice): array
    {
        $config = VerticalFeeConfig::forVertical($vertical);
        if (!$config) {
            return [
                'base_amount' => $salePrice,
                'commission' => 0,
                'vat' => 0,
                'tourism_tax' => 0,
                'service_charge' => 0,
                'total' => $salePrice,
                'listing_fee' => 0,
            ];
        }
        return $config->calculateFees($salePrice);
    }
}
