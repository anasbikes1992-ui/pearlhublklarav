<?php

declare(strict_types=1);

namespace App\DTOs\Booking;

use Carbon\Carbon;

class CreateBookingDTO
{
    public function __construct(
        public string $listingId,
        public Carbon $startAt,
        public Carbon $endAt,
        public float $totalAmount,
        public ?string $currency = 'LKR',
        public ?string $notes = null,
        public ?string $idempotencyKey = null,
        public ?int $guestCount = 1,
        public ?array $specialRequests = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            listingId: $data['listing_id'],
            startAt: Carbon::parse($data['start_at']),
            endAt: Carbon::parse($data['end_at']),
            totalAmount: (float) $data['total_amount'],
            currency: $data['currency'] ?? 'LKR',
            notes: $data['notes'] ?? null,
            idempotencyKey: $data['idempotency_key'] ?? null,
            guestCount: $data['guest_count'] ?? 1,
            specialRequests: $data['special_requests'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'listing_id' => $this->listingId,
            'start_at' => $this->startAt->toDateTimeString(),
            'end_at' => $this->endAt->toDateTimeString(),
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'idempotency_key' => $this->idempotencyKey,
            'guest_count' => $this->guestCount,
            'special_requests' => $this->specialRequests,
        ];
    }
}
