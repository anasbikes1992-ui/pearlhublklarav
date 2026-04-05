<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class PaymentCheckoutController extends BaseApiController
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gateway' => ['required', 'string', 'in:webxpay,genie,koko_pay,mint_pay'],
            'order_id' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'contact_number' => ['nullable', 'string', 'max:40'],
            'address_line_one' => ['nullable', 'string', 'max:255'],
            'address_line_two' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'max:120'],
            'payment_gateway_id' => ['nullable', 'string', 'max:80'],
            'multiple_payment_gateway_ids' => ['nullable', 'string', 'max:255'],
            'custom_fields_raw' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $checkout = $this->paymentService->createCheckout($validated['gateway'], $validated);

            return $this->success($checkout, 'Checkout initialized');
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return $this->error($exception->getMessage(), [], 422);
        }
    }
}
