<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxi_rides', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('driver_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->string('status')->default('searching');
            $table->decimal('pickup_latitude', 10, 7);
            $table->decimal('pickup_longitude', 10, 7);
            $table->decimal('dropoff_latitude', 10, 7);
            $table->decimal('dropoff_longitude', 10, 7);
            $table->decimal('fare_estimate', 12, 2)->nullable();
            $table->decimal('final_fare', 12, 2)->nullable();
            $table->char('currency', 3)->default('LKR');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'driver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxi_rides');
    }
};
