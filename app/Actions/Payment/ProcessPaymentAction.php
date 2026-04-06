<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\DTOs\Payment\CreatePaymentDTO;
use App\Enums\PaymentStatus;
use App\Models\Transaction;
use App\Services\AuditLogService;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;

class ProcessPaymentAction
{
    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    public function execute(CreatePaymentDTO $dto, PaymentGatewayInterface $gateway): Transaction
    {
        return DB::transaction(function () use ($dto, $gateway) {
            // Create transaction record
            $transaction = Transaction::query()->create([
                'user_id' => $dto->userId,
                'booking_id' => $dto->bookingId,
                'amount' => $dto->amount,
                'currency' => $dto->currency ?? 'LKR',
                'gateway' => $gateway->getName(),
                'status' => PaymentStatus::PENDING->value,
                'reference' => $dto->reference,
                'metadata' => $dto->metadata,
            ]);

            // Process with gateway
            try {
                $result = $gateway->createCheckout([
                    'order_id' => $transaction->id,
                    'amount' => $dto->amount,
                    'currency' => $dto->currency ?? 'LKR',
                    'first_name' => $dto->firstName ?? '',
                    'last_name' => $dto->lastName ?? '',
                    'email' => $dto->email ?? '',
                    'contact_number' => $dto->phone ?? '',
                    'return_url' => $dto->returnUrl ?? '',
                    'cancel_url' => $dto->cancelUrl ?? '',
                ]);

                $transaction->gateway_reference = $result['gateway_reference'] ?? null;
                $transaction->gateway_data = $result;
                $transaction->save();

                $this->auditLogService->log(
                    $dto->userId,
                    'payment.initiated',
                    Transaction::class,
                    $transaction->id,
                    [
                        'amount' => $dto->amount,
                        'gateway' => $gateway->getName(),
                        'booking_id' => $dto->bookingId,
                    ]
                );

                return $transaction;
            } catch (\Exception $e) {
                $transaction->status = PaymentStatus::FAILED;
                $transaction->error_message = $e->getMessage();
                $transaction->save();

                throw $e;
            }
        });
    }
}
