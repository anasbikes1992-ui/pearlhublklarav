<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends BaseApiController
{
    public function __construct(private readonly WalletService $walletService)
    {
    }

    public function balance(Request $request): JsonResponse
    {
        $wallet = $this->walletService->getOrCreate($request->user()->id, 'LKR');

        return $this->success([
            'balance' => $wallet->balance,
            'currency' => $wallet->currency,
            'status' => $wallet->status,
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = WalletTransaction::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return $this->success($transactions);
    }
}
