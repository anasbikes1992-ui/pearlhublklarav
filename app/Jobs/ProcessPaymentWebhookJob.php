<?php

namespace App\Jobs;

use App\Services\Payments\PaymentWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @var array<int, int>
     */
    public array $backoff = [30, 120, 300, 900];

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $gateway,
        public string $idempotencyKey,
        public array $payload
    ) {
    }

    public function handle(PaymentWebhookService $paymentWebhookService): void
    {
        $paymentWebhookService->process($this->gateway, $this->idempotencyKey, $this->payload);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Payment webhook processing permanently failed', [
            'gateway' => $this->gateway,
            'idempotency_key' => $this->idempotencyKey,
            'error' => $exception?->getMessage(),
        ]);
    }
}
