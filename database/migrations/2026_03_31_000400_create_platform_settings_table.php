<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('platform_settings')->insert([
            [
                'key' => 'booking_commission_rate',
                'value' => json_encode(['value' => 0.08]),
                'description' => 'Default commission rate used for booking totals.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'property_listing_fee',
                'value' => json_encode(['value' => 0]),
                'description' => 'Configurable property listing fee placeholder.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'buyer_cashback_rate',
                'value' => json_encode(['value' => 0.005]),
                'description' => 'Default buyer cashback rate for promo-confirmed sales.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
