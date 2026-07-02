<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('articles', 'image_id')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->uuid('image_id')->nullable()->after('text');
                $table->foreign('image_id')->references('id')->on('images')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('articles', 'agent_id')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->uuid('agent_id')->nullable()->after('image_id');
                $table->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('articles', 'agent_id')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropForeign(['agent_id']);
                $table->dropColumn('agent_id');
            });
        }

        if (Schema::hasColumn('articles', 'image_id')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropForeign(['image_id']);
                $table->dropColumn('image_id');
            });
        }
    }
};
