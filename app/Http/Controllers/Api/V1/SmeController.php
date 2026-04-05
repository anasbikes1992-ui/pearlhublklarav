<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Listing;
use App\Models\ProviderSalesReport;
use App\Models\SmeProduct;
use App\Services\AuditLogService;
use App\Services\SubscriptionService;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmeController extends BaseApiController
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly TranslationService $translationService,
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan' => ['required', 'string', 'in:silver,gold,platinum'],
        ]);

        $subscription = $this->subscriptionService->createOrRenew($request->user()->id, $validated['plan']);

        return $this->success($subscription, 'Subscription activated', 201);
    }

    public function products(Request $request, Listing $listing): JsonResponse
    {
        if ($listing->provider_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return $this->error('Forbidden', [], 403);
        }

        $items = SmeProduct::query()->where('listing_id', $listing->id)->latest()->paginate(30);

        return $this->success($items);
    }

    public function createProduct(Request $request, Listing $listing): JsonResponse
    {
        if ($listing->provider_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return $this->error('Forbidden', [], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'variants' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
            'stock_status' => ['sometimes', 'string', 'in:in_stock,out_of_stock'],
        ]);

        $count = SmeProduct::query()->where('listing_id', $listing->id)->count();
        $this->subscriptionService->ensureProductLimit($listing->provider_id, $count);

        $translations = [
            'name' => $this->translationService->translateAll($validated['name']),
            'description' => isset($validated['description']) ? $this->translationService->translateAll((string) $validated['description']) : [],
        ];

        $product = SmeProduct::query()->create([
            'listing_id' => $listing->id,
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'variants' => array_merge($validated['variants'] ?? [], ['translations' => $translations]),
            'is_active' => $validated['is_active'] ?? true,
            'stock_status' => $validated['stock_status'] ?? 'in_stock',
        ]);

        $this->auditLogService->log($request->user()->id, 'sme.product.created', SmeProduct::class, $product->id, [
            'listing_id' => $listing->id,
            'category' => $product->category,
        ]);

        return $this->success($product, 'SME product created', 201);
    }

    public function reportSales(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m-01'],
            'total_sales' => ['required', 'numeric', 'min:0'],
        ]);

        $commissionRate = 0;
        $taxRate = 0.18;

        $report = ProviderSalesReport::query()->updateOrCreate(
            [
                'provider_id' => $request->user()->id,
                'month' => $validated['month'],
            ],
            [
                'total_sales' => $validated['total_sales'],
                'commission_due' => $validated['total_sales'] * $commissionRate,
                'tax_applied' => $validated['total_sales'] * $taxRate,
                'verified' => false,
            ]
        );

        $this->auditLogService->log($request->user()->id, 'sme.sales_report.submitted', ProviderSalesReport::class, $report->id, [
            'month' => $validated['month'],
            'total_sales' => $validated['total_sales'],
        ]);

        return $this->success($report, 'Sales report submitted');
    }
}
