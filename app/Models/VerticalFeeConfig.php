<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerticalFeeConfig extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'vertical',
        'listing_fee',
        'commission_rate',
        'vat_rate',
        'tourism_tax_rate',
        'service_charge_rate',
        'is_active',
    ];

    protected $casts = [
        'listing_fee' => 'decimal:2',
        'commission_rate' => 'decimal:4',
        'vat_rate' => 'decimal:4',
        'tourism_tax_rate' => 'decimal:4',
        'service_charge_rate' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public static function forVertical(string $vertical): ?self
    {
        return static::where('vertical', $vertical)->where('is_active', true)->first();
    }

    public function calculateFees(float $baseAmount): array
    {
        $commission = $baseAmount * (float) $this->commission_rate;
        $subtotalAfterCommission = $baseAmount + $commission;
        $vat = $subtotalAfterCommission * (float) $this->vat_rate;
        $tourismTax = $subtotalAfterCommission * (float) $this->tourism_tax_rate;
        $serviceCharge = $subtotalAfterCommission * (float) $this->service_charge_rate;

        return [
            'base_amount' => round($baseAmount, 2),
            'commission' => round($commission, 2),
            'vat' => round($vat, 2),
            'tourism_tax' => round($tourismTax, 2),
            'service_charge' => round($serviceCharge, 2),
            'total' => round($baseAmount + $commission + $vat + $tourismTax + $serviceCharge, 2),
            'listing_fee' => round((float) $this->listing_fee, 2),
        ];
    }
}
