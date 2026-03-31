<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CashbackRecord;
use App\Services\CashbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashbackController extends BaseApiController
{
    public function __construct(
        private CashbackService $cashbackService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = CashbackRecord::query();

        if ($user->role === 'provider') {
            $query->where('provider_id', $user->id);
        } elseif ($user->role === 'customer') {
            $query->where('customer_id', $user->id);
        }

        return $this->success($query->with(['booking', 'customer', 'provider'])->latest()->paginate(20));
    }

    public function confirm(Request $request, CashbackRecord $cashbackRecord): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $cashbackRecord->provider_id !== $user->id) {
            return $this->error('Unauthorized', [], 403);
        }

        if ($cashbackRecord->status !== 'pending') {
            return $this->error('Cashback already processed', [], 422);
        }

        $record = $this->cashbackService->confirmByProvider($cashbackRecord);

        return $this->success($record, 'Cashback confirmed');
    }

    public function credit(Request $request, CashbackRecord $cashbackRecord): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Admin access required', [], 403);
        }

        if ($cashbackRecord->status !== 'approved') {
            return $this->error('Cashback must be approved first', [], 422);
        }

        $record = $this->cashbackService->creditToWallet($cashbackRecord);

        return $this->success($record, 'Cashback credited to wallet');
    }
}
