<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignUuid('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('provider');
            $table->string('external_reference')->nullable();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('LKR');
            $table->string('status')->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['provider', 'external_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
