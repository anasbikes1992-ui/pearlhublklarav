<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Profile;
use App\Models\OwnershipDocument;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * KYC Service - Know Your Customer verification
 * Handles document verification, identity checks, and compliance
 */
class KycService
{
    private AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Submit KYC documents for verification
     */
    public function submitDocuments(User $user, array $documents): array
    {
        $results = [];

        foreach ($documents as $type => $file) {
            // Store document
            $path = $file->store("kyc/{$user->id}", 'private');

            $document = OwnershipDocument::query()->create([
                'user_id' => $user->id,
                'type' => $type,
                'file_path' => $path,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            $results[$type] = [
                'document_id' => $document->id,
                'status' => 'pending',
                'path' => $path,
            ];
        }

        // Update profile KYC status
        $user->profile->update(['kyc_status' => 'pending']);

        $this->auditLogService->log(
            $user->id,
            'kyc.submitted',
            User::class,
            $user->id,
            ['document_types' => array_keys($documents)]
        );

        return $results;
    }

    /**
     * Verify KYC documents (admin action)
     */
    public function verifyDocuments(User $user, string $status, string $notes = ''): void
    {
        if (! in_array($status, ['verified', 'rejected', 'additional_info_needed'])) {
            throw new \InvalidArgumentException('Invalid KYC status');
        }

        $previousStatus = $user->profile->kyc_status;

        $user->profile->update([
            'kyc_status' => $status,
            'kyc_verified_at' => $status === 'verified' ? now() : null,
            'kyc_notes' => $notes,
        ]);

        // Update all pending documents
        if ($status === 'verified') {
            $user->ownershipDocuments()
                ->where('status', 'pending')
                ->update(['status' => 'verified', 'verified_at' => now()]);

            // Award referral bonus if applicable
            $this->handleVerifiedReferral($user);
        } elseif ($status === 'rejected') {
            $user->ownershipDocuments()
                ->where('status', 'pending')
                ->update(['status' => 'rejected', 'notes' => $notes]);
        }

        $this->auditLogService->log(
            auth()->id(),
            'kyc.verification',
            User::class,
            $user->id,
            [
                'previous_status' => $previousStatus,
                'new_status' => $status,
                'notes' => $notes,
            ]
        );
    }

    /**
     * Check if user is KYC verified
     */
    public function isVerified(User $user): bool
    {
        return $user->profile?->kyc_status === 'verified';
    }

    /**
     * Get KYC verification level
     */
    public function getVerificationLevel(User $user): string
    {
        if (! $this->isVerified($user)) {
            return 'none';
        }

        $verifiedDocuments = $user->ownershipDocuments()
            ->where('status', 'verified')
            ->count();

        return match (true) {
            $verifiedDocuments >= 3 => 'full',
            $verifiedDocuments >= 2 => 'enhanced',
            $verifiedDocuments >= 1 => 'basic',
            default => 'none',
        };
    }

    /**
     * Get KYC status label
     */
    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'verified' => 'Verified',
            'pending' => 'Pending Review',
            'rejected' => 'Rejected',
            'additional_info_needed' => 'Additional Info Needed',
            default => 'Unknown',
        };
    }

    /**
     * Check if KYC required for action
     */
    public function isRequiredFor(User $user, string $action): bool
    {
        return match ($action) {
            'withdraw' => true,
            'list_property' => true,
            'receive_payouts' => true,
            'high_value_booking' => true,
            default => false,
        };
    }

    /**
     * Validate document before submission
     */
    public function validateDocument($file, string $type): array
    {
        $errors = [];

        // Check file size (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            $errors[] = 'File size must be less than 10MB';
        }

        // Check file type
        $allowedTypes = match ($type) {
            'nic', 'passport', 'driving_license' => ['image/jpeg', 'image/png', 'application/pdf'],
            'business_registration', 'utility_bill' => ['image/jpeg', 'image/png', 'application/pdf'],
            default => ['image/jpeg', 'image/png'],
        };

        if (! in_array($file->getMimeType(), $allowedTypes)) {
            $errors[] = 'Invalid file type. Allowed: JPG, PNG, PDF';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get pending KYC submissions for admin review
     */
    public function getPendingSubmissions(int $limit = 50): array
    {
        return Profile::query()
            ->where('kyc_status', 'pending')
            ->with(['user', 'user.ownershipDocuments'])
            ->orderBy('updated_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(fn ($profile) => [
                'user_id' => $profile->user_id,
                'full_name' => $profile->user->full_name,
                'email' => $profile->user->email,
                'submitted_at' => $profile->updated_at,
                'documents' => $profile->user->ownershipDocuments
                    ->where('status', 'pending')
                    ->map(fn ($doc) => [
                        'id' => $doc->id,
                        'type' => $doc->type,
                        'submitted_at' => $doc->submitted_at,
                    ])
                    ->toArray(),
            ])
            ->toArray();
    }

    /**
     * Get KYC statistics for admin dashboard
     */
    public function getStats(): array
    {
        return [
            'pending' => Profile::query()->where('kyc_status', 'pending')->count(),
            'verified' => Profile::query()->where('kyc_status', 'verified')->count(),
            'rejected' => Profile::query()->where('kyc_status', 'rejected')->count(),
            'additional_info_needed' => Profile::query()
                ->where('kyc_status', 'additional_info_needed')
                ->count(),
            'average_verification_time' => $this->getAverageVerificationTime(),
        ];
    }

    /**
     * Handle referral bonus for verified users
     */
    private function handleVerifiedReferral(User $user): void
    {
        // Check if user was referred
        $referral = $user->referredBy?->referralsMade()
            ->where('referred_id', $user->id)
            ->where('referral_type', 'verified')
            ->first();

        if ($referral && $referral->isPending()) {
            // Award bonus through referral service
            app(ReferralService::class)->markQualified(
                $referral,
                'kyc_verified',
                config('referral.verified_bonus', 0)
            );
        }
    }

    /**
     * Calculate average verification time in hours
     */
    private function getAverageVerificationTime(): ?float
    {
        $avgSeconds = Profile::query()
            ->where('kyc_status', 'verified')
            ->whereNotNull('kyc_verified_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, updated_at, kyc_verified_at)) as avg_seconds')
            ->value('avg_seconds');

        return $avgSeconds ? round($avgSeconds / 3600, 2) : null;
    }

    /**
     * Request additional documents from user
     */
    public function requestAdditionalDocuments(User $user, array $documentTypes, string $message): void
    {
        $user->profile->update([
            'kyc_status' => 'additional_info_needed',
            'kyc_notes' => $message,
        ]);

        // TODO: Send notification to user

        $this->auditLogService->log(
            auth()->id(),
            'kyc.additional_documents_requested',
            User::class,
            $user->id,
            ['document_types' => $documentTypes, 'message' => $message]
        );
    }
}
