<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recover from partial creation when a previous run failed mid-migration.
        Schema::dropIfExists('social_follows');
        Schema::dropIfExists('social_likes');

        Schema::create('social_likes', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('post_id');
            $table->foreign('post_id')->references('id')->on('social_posts')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'post_id']);
        });

        Schema::create('social_follows', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('following_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['follower_id', 'following_id']);
            $table->index('following_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_follows');
        Schema::dropIfExists('social_likes');
    }
};
