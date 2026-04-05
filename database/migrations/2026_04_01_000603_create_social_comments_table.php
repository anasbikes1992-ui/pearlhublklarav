<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recover from partial creation when a previous run failed mid-migration.
        Schema::dropIfExists('social_comments');

        Schema::create('social_comments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('post_id');
            $table->foreign('post_id')->references('id')->on('social_posts')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->uuid('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('social_comments')->cascadeOnDelete();
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();

            $table->index(['post_id', 'created_at']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_comments');
    }
};
