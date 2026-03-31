<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashback_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('sale_amount', 14, 2);
            $table->decimal('cashback_rate', 5, 4); // e.g. 0.005
            $table->decimal('cashback_amount', 14, 2);
            $table->string('currency', 3)->default('LKR');
            $table->enum('status', ['pending', 'approved', 'credited', 'rejected'])->default('pending');
            $table->timestamp('confirmed_at')->nullable(); // provider confirms
            $table->timestamp('credited_at')->nullable(); // wallet credited
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashback_records');
    }
};
