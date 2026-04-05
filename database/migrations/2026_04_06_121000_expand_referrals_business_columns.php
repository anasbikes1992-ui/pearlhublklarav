<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table): void {
            if (!Schema::hasColumn('referrals', 'bonus_paid_at')) {
                $table->dateTime('bonus_paid_at')->nullable()->after('revenue_bonus_amount');
            }
            if (!Schema::hasColumn('referrals', 'bonus_currency')) {
                $table->string('bonus_currency', 8)->default('LKR')->after('bonus_paid_at');
            }
            if (!Schema::hasColumn('referrals', 'referral_type')) {
                $table->string('referral_type', 30)->default('signup')->after('bonus_currency');
            }
            if (!Schema::hasColumn('referrals', 'qualified_action')) {
                $table->string('qualified_action', 100)->nullable()->after('referral_type');
            }
            if (!Schema::hasColumn('referrals', 'qualified_at')) {
                $table->dateTime('qualified_at')->nullable()->after('qualified_action');
            }
            if (!Schema::hasColumn('referrals', 'expires_at')) {
                $table->dateTime('expires_at')->nullable()->after('qualified_at');
            }
            if (!Schema::hasColumn('referrals', 'metadata')) {
                $table->json('metadata')->nullable()->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table): void {
            $dropColumns = [];
            foreach (['bonus_paid_at', 'bonus_currency', 'referral_type', 'qualified_action', 'qualified_at', 'expires_at', 'metadata'] as $column) {
                if (Schema::hasColumn('referrals', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
