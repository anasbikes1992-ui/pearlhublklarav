<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vertical_fee_configs', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('vertical');
            $table->string('icon', 10)->nullable()->after('display_name');
            $table->string('color', 20)->nullable()->after('icon');
            $table->string('flow_type', 30)->default('booking')->after('service_charge_rate');
            $table->integer('cancellation_window_hours')->default(24)->after('flow_type');
            $table->integer('buffer_hours')->default(0)->after('cancellation_window_hours');
            $table->boolean('requires_escrow')->default(true)->after('buffer_hours');
            $table->integer('product_limit')->nullable()->after('requires_escrow');
        });
    }

    public function down(): void
    {
        Schema::table('vertical_fee_configs', function (Blueprint $table) {
            $table->dropColumn([
                'display_name',
                'icon',
                'color',
                'flow_type',
                'cancellation_window_hours',
                'buffer_hours',
                'requires_escrow',
                'product_limit',
            ]);
        });
    }
};
