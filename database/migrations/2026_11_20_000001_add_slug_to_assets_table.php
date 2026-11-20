<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('assets', 'slug')) {
            return;
        }

        Schema::table('assets', function (Blueprint $table) {
            $table->string('slug', 500)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('assets', 'slug')) {
            return;
        }

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
