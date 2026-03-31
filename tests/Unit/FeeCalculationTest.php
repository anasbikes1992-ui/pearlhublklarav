<?php

namespace Tests\Unit;

use App\Models\VerticalFeeConfig;
use PHPUnit\Framework\TestCase;

class FeeCalculationTest extends TestCase
{
    public function test_property_fee_calculation(): void
    {
        $config = new VerticalFeeConfig([
            'vertical' => 'property',
            'listing_fee' => 0,
            'commission_rate' => 0.02,
            'vat_rate' => 0,
            'tourism_tax_rate' => 0,
            'service_charge_rate' => 0,
            'is_active' => true,
        ]);

        $fees = $config->calculateFees(50000000); // 50M LKR property

        $this->assertEquals(50000000, $fees['base_amount']);
        $this->assertEquals(1000000, $fees['commission']); // 2%
        $this->assertEquals(51000000, $fees['total']);
        $this->assertEquals(0, $fees['listing_fee']);
    }

    public function test_stay_fee_calculation_with_taxes(): void
    {
        $config = new VerticalFeeConfig([
            'vertical' => 'stay',
            'listing_fee' => 0,
            'commission_rate' => 0.08,
            'vat_rate' => 0.15,
            'tourism_tax_rate' => 0.01,
            'service_charge_rate' => 0.10,
            'is_active' => true,
        ]);

        $fees = $config->calculateFees(10000); // 10k LKR night

        $this->assertEquals(10000, $fees['base_amount']);
        $this->assertEquals(800, $fees['commission']); // 8%
        $this->assertGreaterThan(0, $fees['vat']);
        $this->assertGreaterThan(0, $fees['tourism_tax']);
        $this->assertGreaterThan(0, $fees['service_charge']);
        $this->assertGreaterThan(10800, $fees['total']);
    }

    public function test_promo_code_validation(): void
    {
        $promo = new \App\Models\PromoCode([
            'code' => 'TEST',
            'type' => 'discount_fixed',
            'value' => 500,
            'max_uses' => 10,
            'used_count' => 0,
            'is_active' => true,
            'expires_at' => now()->addMonth(),
        ]);

        $this->assertTrue($promo->isValid());
    }

    public function test_expired_promo_code_is_invalid(): void
    {
        $promo = new \App\Models\PromoCode([
            'code' => 'EXPIRED',
            'type' => 'discount_fixed',
            'value' => 500,
            'max_uses' => 10,
            'used_count' => 0,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($promo->isValid());
    }

    public function test_fully_used_promo_code_is_invalid(): void
    {
        $promo = new \App\Models\PromoCode([
            'code' => 'USED',
            'type' => 'discount_fixed',
            'value' => 500,
            'max_uses' => 5,
            'used_count' => 5,
            'is_active' => true,
        ]);

        $this->assertFalse($promo->isValid());
    }
}
