<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('social_posts')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver !== 'mysql') {
            return;
        }

        $column = DB::selectOne("SHOW COLUMNS FROM social_posts WHERE Field = 'user_id'");

        if (! $column || ! str_contains(strtolower((string) $column->Type), 'bigint')) {
            return;
        }

        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'social_posts' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME IS NOT NULL");

        foreach ($foreignKeys as $foreignKey) {
            DB::statement('ALTER TABLE social_posts DROP FOREIGN KEY ' . $foreignKey->CONSTRAINT_NAME);
        }

        DB::statement('ALTER TABLE social_posts MODIFY user_id CHAR(36) NOT NULL');

        $hasUserForeignKey = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'social_posts' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME = 'users' LIMIT 1");

        if (! $hasUserForeignKey) {
            DB::statement('ALTER TABLE social_posts ADD CONSTRAINT social_posts_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        }
    }

    public function down(): void
    {
        // One-way data correction migration.
    }
};
