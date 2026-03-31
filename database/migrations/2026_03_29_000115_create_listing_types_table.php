<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('listing_id')->unique()->constrained('listings')->cascadeOnDelete();
            $table->string('type');

            $table->integer('sqft')->nullable();
            $table->integer('beds')->nullable();
            $table->integer('baths')->nullable();
            $table->string('deed_status')->nullable();

            $table->string('room_type')->nullable();
            $table->json('amenities_json')->nullable();
            $table->json('check_in_out_times')->nullable();

            $table->string('transmission')->nullable();
            $table->string('fuel_type')->nullable();
            $table->decimal('daily_rate', 12, 2)->nullable();
            $table->boolean('with_driver')->default(false);

            $table->json('venue_map_json')->nullable();
            $table->json('ticket_tiers_json')->nullable();
            $table->string('qr_secret')->nullable();

            $table->json('extra_json')->nullable();
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_types');
    }
};
