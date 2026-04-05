<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('social_posts')) {
            return;
        }

        Schema::create('social_posts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->json('media_urls')->nullable();
            $table->string('vertical_tag', 30)->nullable()
                ->comment('property|stays|vehicles|events|sme|taxi|experience|social');
            $table->uuid('listing_id')->nullable()
                ->comment('Attached marketplace listing (cross-table FK, not enforced)');
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['vertical_tag', 'created_at']);
            $table->index('is_flagged');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
