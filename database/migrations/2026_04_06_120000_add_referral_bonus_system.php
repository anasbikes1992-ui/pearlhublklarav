<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 32)->nullable()->unique()->after('phone');
            }
            if (!Schema::hasColumn('users', 'referred_by_user_id')) {
                $table->foreignUuid('referred_by_user_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            }
        });

        if (!Schema::hasTable('pearl_points')) {
            Schema::create('pearl_points', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->unsignedInteger('total_earned')->default(0);
                $table->unsignedInteger('total_redeemed')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('referrals')) {
            Schema::create('referrals', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('referrer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('referred_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('code', 32)->index();
                $table->string('status', 40)->default('registered')->index();
                $table->unsignedInteger('points_awarded')->default(0);
                $table->decimal('revenue_bonus_amount', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        DB::table('platform_settings')->updateOrInsert(
            ['key' => 'referral_bonus_referrer_points'],
            [
                'value' => json_encode(['value' => 100]),
                'description' => 'Points awarded to referrer when a new user registers with a valid referral code.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('platform_settings')->updateOrInsert(
            ['key' => 'referral_bonus_referred_points'],
            [
                'value' => json_encode(['value' => 25]),
                'description' => 'Welcome points awarded to referred user.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('platform_settings')->updateOrInsert(
            ['key' => 'referral_bonus_referrer_cash_lkr'],
            [
                'value' => json_encode(['value' => 0]),
                'description' => 'Optional LKR cash bonus credited to referrer wallet for each successful referral.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('referrals')) {
            Schema::drop('referrals');
        }

        if (Schema::hasTable('pearl_points')) {
            Schema::drop('pearl_points');
        }

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'referred_by_user_id')) {
                $table->dropConstrainedForeignId('referred_by_user_id');
            }
            if (Schema::hasColumn('users', 'referral_code')) {
                $table->dropUnique(['referral_code']);
                $table->dropColumn('referral_code');
            }
        });
    }
};
