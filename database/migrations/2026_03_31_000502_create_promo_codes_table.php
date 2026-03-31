<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->foreignUuid('listing_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('issued_by')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['sale_confirmation', 'discount_fixed', 'discount_percent']);
            $table->decimal('value', 12, 2)->default(0); // discount amount or percent
            $table->string('vertical')->nullable();
            $table->integer('max_uses')->default(1);
            $table->integer('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['code', 'is_active']);
            $table->index(['listing_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
