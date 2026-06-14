<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sessions')) {
            return;
        }

        DB::statement('ALTER TABLE `sessions` MODIFY `user_id` CHAR(36) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('sessions')) {
            return;
        }

        DB::statement('ALTER TABLE `sessions` MODIFY `user_id` BIGINT UNSIGNED NULL');
    }
};
