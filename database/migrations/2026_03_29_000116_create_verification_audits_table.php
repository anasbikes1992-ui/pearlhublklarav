<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_audits', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->foreignUuid('inspector_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('approved');
            $table->text('notes')->nullable();
            $table->timestamp('inspected_at');
            $table->json('photo_urls')->nullable();
            $table->timestamps();

            $table->index(['listing_id', 'inspected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_audits');
    }
};
