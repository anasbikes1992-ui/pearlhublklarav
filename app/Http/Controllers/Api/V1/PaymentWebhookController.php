<?php

namespace App\Http\Controllers\Api\V1;

use App\Jobs\ProcessPaymentWebhookJob;
use App\Services\Payments\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends BaseApiController
{
    public function __construct(private readonly PaymentWebhookService $paymentWebhookService)
    {
    }

    public function webxpay(Request $request): JsonResponse
    {
        return $this->handleGateway($request, 'webxpay', 'X-WebXPay-Signature');
    }

    public function dialogGenie(Request $request): JsonResponse
    {
        return $this->handleGateway($request, 'dialog_genie', 'X-Dialog-Genie-Signature');
    }

    private function handleGateway(Request $request, string $gateway, string $signatureHeader): JsonResponse
    {
        $payload = $request->all();

        $record = $this->paymentWebhookService->verifyAndReserve($gateway, $payload, [
            'signature' => (string) $request->header($signatureHeader, ''),
            'raw_body' => $request->getContent(),
        ]);

        if ($record->status === 'processed') {
            return $this->success(['idempotency_key' => $record->key], 'Already processed');
        }

        ProcessPaymentWebhookJob::dispatch($gateway, $record->key, $payload)->onQueue('payments');

        return $this->success(['idempotency_key' => $record->key], 'Accepted', 202);
    }
}
