<?php

namespace App\Services;

use App\Models\PromoCode;
use Illuminate\Support\Str;

class PromoCodeService
{
    public function generate(string $userId, array $data): PromoCode
    {
        return PromoCode::create([
            'code' => $data['code'] ?? strtoupper(Str::random(8)),
            'listing_id' => $data['listing_id'] ?? null,
            'issued_by' => $userId,
            'type' => $data['type'],
            'value' => $data['value'] ?? 0,
            'vertical' => $data['vertical'] ?? null,
            'max_uses' => $data['max_uses'] ?? 1,
            'expires_at' => $data['expires_at'] ?? null,
        ]);
    }

    public function validate(string $code, ?string $vertical = null, ?float $amount = null): array
    {
        $promo = PromoCode::where('code', $code)->first();

        if (!$promo) {
            return ['valid' => false, 'error' => 'Promo code not found'];
        }

        if (!$promo->isValid()) {
            return ['valid' => false, 'error' => 'Promo code is expired or fully used'];
        }

        if ($vertical && $promo->vertical && $promo->vertical !== $vertical) {
            return ['valid' => false, 'error' => 'Promo code not valid for this vertical'];
        }

        $discount = 0;
        if ($promo->type === 'discount_fixed') {
            $discount = (float) $promo->value;
        } elseif ($promo->type === 'discount_percent' && $amount) {
            $discount = $amount * ((float) $promo->value / 100);
        }

        return [
            'valid' => true,
            'promo' => $promo,
            'type' => $promo->type,
            'discount' => round($discount, 2),
        ];
    }

    public function redeem(string $code): PromoCode
    {
        $promo = PromoCode::where('code', $code)->firstOrFail();
        $promo->redeem();
        return $promo;
    }
}
