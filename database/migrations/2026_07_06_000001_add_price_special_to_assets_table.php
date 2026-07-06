<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('assets', 'price_special')) {
            return;
        }

        Schema::table('assets', function (Blueprint $table): void {
            $table->decimal('price_special', 15, 2)->nullable()->after('price_amounnt');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('assets', 'price_special')) {
            return;
        }

        Schema::table('assets', function (Blueprint $table): void {
            $table->dropColumn('price_special');
        });
    }
};
