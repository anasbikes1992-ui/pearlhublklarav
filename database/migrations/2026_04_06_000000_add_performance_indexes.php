<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Composite index for public listing search (most common query)
        Schema::table('listings', function (Blueprint $table): void {
            $table->index(['vertical', 'status', 'is_hidden'], 'idx_listings_search');
            $table->index(['provider_id', 'status', 'created_at'], 'idx_listings_provider_dashboard');
            $table->index(['slug'], 'idx_listings_slug_lookup');
            $table->index(['seo_slug'], 'idx_listings_seo_slug');
        });

        // Booking conflict detection and user queries
        Schema::table('bookings', function (Blueprint $table): void {
            $table->index(['listing_id', 'status', 'start_at', 'end_at'], 'idx_bookings_conflict_check');
            $table->index(['customer_id', 'created_at'], 'idx_bookings_user_history');
            $table->index(['status', 'created_at'], 'idx_bookings_status_recent');
        });

        // Transaction queries
        Schema::table('transactions', function (Blueprint $table): void {
            $table->index(['wallet_id', 'created_at'], 'idx_transactions_wallet_recent');
            $table->index(['status', 'created_at'], 'idx_transactions_status_recent');
        });

        // Search performance
        Schema::table('listings', function (Blueprint $table): void {
            $table->index(['latitude', 'longitude'], 'idx_listings_geo');
        });

        // Social feed performance
        Schema::table('social_posts', function (Blueprint $table): void {
            $table->index(['is_pinned', 'created_at'], 'idx_posts_feed_sort');
            $table->index(['vertical_tag', 'created_at'], 'idx_posts_vertical');
        });

        // User lookup by role
        Schema::table('users', function (Blueprint $table): void {
            $table->index(['role', 'is_active', 'created_at'], 'idx_users_admin_lookup');
            $table->index(['email'], 'idx_users_email_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            $table->dropIndex('idx_listings_search');
            $table->dropIndex('idx_listings_provider_dashboard');
            $table->dropIndex('idx_listings_slug_lookup');
            $table->dropIndex('idx_listings_seo_slug');
            $table->dropIndex('idx_listings_geo');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex('idx_bookings_conflict_check');
            $table->dropIndex('idx_bookings_user_history');
            $table->dropIndex('idx_bookings_status_recent');
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropIndex('idx_transactions_wallet_recent');
            $table->dropIndex('idx_transactions_status_recent');
        });

        Schema::table('social_posts', function (Blueprint $table): void {
            $table->dropIndex('idx_posts_feed_sort');
            $table->dropIndex('idx_posts_vertical');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('idx_users_admin_lookup');
            $table->dropIndex('idx_users_email_lookup');
        });
    }
};
