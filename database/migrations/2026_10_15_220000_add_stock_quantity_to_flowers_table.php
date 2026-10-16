<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flowers', function (Blueprint $table) {
            $table->unsignedInteger('stock_quantity')->default(0)->after('price_per_stem');
        });

        // Backfill existing rows with a generous starting stock rather than
        // 0 — otherwise every flower seeded before this column existed
        // would instantly become unorderable. An admin can then dial each
        // one in to the real number from Flowers > edit.
        DB::table('flowers')->update(['stock_quantity' => 50]);
    }

    public function down(): void
    {
        Schema::table('flowers', function (Blueprint $table) {
            $table->dropColumn('stock_quantity');
        });
    }
};
