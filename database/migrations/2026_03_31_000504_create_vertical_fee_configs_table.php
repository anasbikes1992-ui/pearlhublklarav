<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vertical_fee_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('vertical'); // property, stay, vehicle, event, sme
            $table->decimal('listing_fee', 12, 2)->default(0);
            $table->decimal('commission_rate', 5, 4)->default(0.08); // e.g. 0.02
            $table->decimal('vat_rate', 5, 4)->default(0); // e.g. 0.15
            $table->decimal('tourism_tax_rate', 5, 4)->default(0); // SL-specific
            $table->decimal('service_charge_rate', 5, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('vertical');
        });

        // Seed default configs for each vertical
        DB::table('vertical_fee_configs')->insert([
            [
                'id' => Str::uuid(),
                'vertical' => 'property',
                'listing_fee' => 0,
                'commission_rate' => 0.02,
                'vat_rate' => 0,
                'tourism_tax_rate' => 0,
                'service_charge_rate' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'vertical' => 'stay',
                'listing_fee' => 0,
                'commission_rate' => 0.08,
                'vat_rate' => 0.15,
                'tourism_tax_rate' => 0.01,
                'service_charge_rate' => 0.10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'vertical' => 'vehicle',
                'listing_fee' => 0,
                'commission_rate' => 0.05,
                'vat_rate' => 0.15,
                'tourism_tax_rate' => 0,
                'service_charge_rate' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'vertical' => 'event',
                'listing_fee' => 0,
                'commission_rate' => 0.05,
                'vat_rate' => 0.15,
                'tourism_tax_rate' => 0,
                'service_charge_rate' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'vertical' => 'sme',
                'listing_fee' => 0,
                'commission_rate' => 0.03,
                'vat_rate' => 0.15,
                'tourism_tax_rate' => 0,
                'service_charge_rate' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vertical_fee_configs');
    }
};
