<?php

declare(strict_types=1);

namespace App\DTOs\Payment;

class CreatePaymentDTO
{
    public function __construct(
        public string $userId,
        public float $amount,
        public ?string $bookingId = null,
        public ?string $currency = 'LKR',
        public ?string $reference = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $returnUrl = null,
        public ?string $cancelUrl = null,
        public ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            amount: (float) $data['amount'],
            bookingId: $data['booking_id'] ?? null,
            currency: $data['currency'] ?? 'LKR',
            reference: $data['reference'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            returnUrl: $data['return_url'] ?? null,
            cancelUrl: $data['cancel_url'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'booking_id' => $this->bookingId,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'return_url' => $this->returnUrl,
            'cancel_url' => $this->cancelUrl,
            'metadata' => $this->metadata,
        ];
    }
}
