<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('vertical_fee_configs')->insertOrIgnore([
            'id'                   => Str::uuid(),
            'vertical'             => 'experience',
            'listing_fee'          => 0,
            'commission_rate'      => 0.06,  // 6% commission
            'vat_rate'             => 0.15,  // 15% VAT
            'tourism_tax_rate'     => 0.02,  // 2% tourism levy (higher — tourism-facing)
            'service_charge_rate'  => 0.05,  // 5% service charge
            'is_active'            => true,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('vertical_fee_configs')->where('vertical', 'experience')->delete();
    }
};
