<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agents') || Schema::hasColumn('agents', 'frontend_url')) {
            return;
        }

        Schema::table('agents', function (Blueprint $table): void {
            $table->string('frontend_url', 255)->nullable()->after('system_type');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('agents') || ! Schema::hasColumn('agents', 'frontend_url')) {
            return;
        }

        Schema::table('agents', function (Blueprint $table): void {
            $table->dropColumn('frontend_url');
        });
    }
};
