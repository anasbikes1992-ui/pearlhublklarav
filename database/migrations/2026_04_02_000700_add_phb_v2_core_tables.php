<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        // Recover from partial creation when a previous run failed mid-migration.
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('ai_concierge_logs');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('provider_sales_reports');
        Schema::dropIfExists('sme_products');
        Schema::dropIfExists('sme_subscriptions');

        Schema::table('listings', function (Blueprint $table) use ($driver): void {
            if (! Schema::hasColumn('listings', 'availability_calendar')) {
                if ($driver === 'pgsql') {
                    $table->jsonb('availability_calendar')->nullable()->after('metadata');
                } else {
                    $table->json('availability_calendar')->nullable()->after('metadata');
                }
            }

            if (! Schema::hasColumn('listings', 'seo_slug')) {
                $table->string('seo_slug', 220)->nullable()->after('slug');
                $table->unique('seo_slug');
            }
        });

        // Enforce vertical enum semantics with DB-level check constraints without requiring type changes.
        $allowedVerticals = "'property','stay','vehicle','taxi','event','sme'";
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE listings DROP CONSTRAINT IF EXISTS listings_vertical_check");
            DB::statement("ALTER TABLE listings ADD CONSTRAINT listings_vertical_check CHECK (vertical IN ({$allowedVerticals}))");
            DB::statement('CREATE INDEX IF NOT EXISTS listings_availability_calendar_gin ON listings USING GIN (availability_calendar)');
        }

        Schema::create('sme_subscriptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->enum('plan', ['silver', 'gold', 'platinum']);
            $table->timestamp('expires_at');
            $table->unsignedInteger('product_limit');
            $table->enum('status', ['active', 'expired', 'suspended'])->default('active');
            $table->timestamps();

            $table->index(['provider_id', 'status']);
        });

        Schema::create('sme_products', function (Blueprint $table) use ($driver): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('name', 180);
            $table->string('category', 120);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            if ($driver === 'pgsql') {
                $table->jsonb('variants')->nullable();
            } else {
                $table->json('variants')->nullable();
            }
            $table->boolean('is_active')->default(true);
            $table->enum('stock_status', ['in_stock', 'out_of_stock'])->default('in_stock');
            $table->timestamps();

            $table->index(['listing_id', 'category']);
            $table->index(['is_active', 'stock_status']);
        });

        if ($driver === 'pgsql') {
            DB::statement('CREATE INDEX IF NOT EXISTS sme_products_variants_gin ON sme_products USING GIN (variants)');
        }

        Schema::create('provider_sales_reports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->date('month');
            $table->decimal('total_sales', 14, 2)->default(0);
            $table->decimal('commission_due', 14, 2)->default(0);
            $table->decimal('tax_applied', 14, 2)->default(0);
            $table->boolean('verified')->default(false);
            $table->timestamps();

            $table->unique(['provider_id', 'month']);
        });

        Schema::create('messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->foreignUuid('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->boolean('is_voice')->default(false);
            $table->text('original_text')->nullable();
            $table->text('translated_text')->nullable();
            $table->timestamps();

            $table->index(['listing_id', 'created_at']);
            $table->index(['sender_id', 'receiver_id']);
        });

        Schema::create('ai_concierge_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('query');
            $table->longText('response');
            $table->string('model_used', 120);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) use ($driver): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 120);
            $table->string('entity_type', 120);
            $table->string('entity_id', 120)->nullable();
            if ($driver === 'pgsql') {
                $table->jsonb('meta')->nullable();
            } else {
                $table->json('meta')->nullable();
            }
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['action', 'created_at']);
        });

        if ($driver === 'pgsql') {
            DB::statement('CREATE INDEX IF NOT EXISTS audit_logs_meta_gin ON audit_logs USING GIN (meta)');
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('ai_concierge_logs');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('provider_sales_reports');
        Schema::dropIfExists('sme_products');
        Schema::dropIfExists('sme_subscriptions');

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS listings_availability_calendar_gin');
            DB::statement('ALTER TABLE listings DROP CONSTRAINT IF EXISTS listings_vertical_check');
        }

        Schema::table('listings', function (Blueprint $table): void {
            if (Schema::hasColumn('listings', 'seo_slug')) {
                $table->dropUnique(['seo_slug']);
                $table->dropColumn('seo_slug');
            }

            if (Schema::hasColumn('listings', 'availability_calendar')) {
                $table->dropColumn('availability_calendar');
            }
        });
    }
};
