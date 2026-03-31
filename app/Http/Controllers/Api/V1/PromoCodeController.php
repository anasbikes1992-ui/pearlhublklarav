<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\PromoCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoCodeController extends BaseApiController
{
    public function __construct(
        private PromoCodeService $promoCodeService,
    ) {}

    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:32',
            'vertical' => 'nullable|string',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $result = $this->promoCodeService->validate(
            $request->input('code'),
            $request->input('vertical'),
            $request->input('amount'),
        );

        if (!$result['valid']) {
            return $this->error($result['error'], [], 422);
        }

        return $this->success([
            'valid' => true,
            'type' => $result['type'],
            'discount' => $result['discount'],
        ]);
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:sale_confirmation,discount_fixed,discount_percent',
            'code' => 'nullable|string|max:32|unique:promo_codes,code',
            'listing_id' => 'nullable|uuid|exists:listings,id',
            'value' => 'nullable|numeric|min:0',
            'vertical' => 'nullable|string',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $promo = $this->promoCodeService->generate(
            $request->user()->id,
            $request->validated(),
        );

        return $this->success($promo, 'Promo code created', 201);
    }

    public function redeem(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $promo = $this->promoCodeService->redeem($request->input('code'));

        return $this->success($promo, 'Promo code redeemed');
    }

    public function index(Request $request): JsonResponse
    {
        $query = \App\Models\PromoCode::query();

        if ($request->user()->role !== 'admin') {
            $query->where('issued_by', $request->user()->id);
        }

        return $this->success($query->latest()->paginate(20));
    }
}
