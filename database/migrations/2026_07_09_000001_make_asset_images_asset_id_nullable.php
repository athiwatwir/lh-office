<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('asset_images') || ! Schema::hasColumn('asset_images', 'asset_id')) {
            return;
        }

        DB::statement('ALTER TABLE asset_images MODIFY asset_id CHAR(36) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('asset_images') || ! Schema::hasColumn('asset_images', 'customer_asset_id')) {
            return;
        }

        DB::statement('UPDATE asset_images SET asset_id = customer_asset_id WHERE asset_id IS NULL AND customer_asset_id IS NOT NULL');
        DB::statement('ALTER TABLE asset_images MODIFY asset_id CHAR(36) NOT NULL');
    }
};
