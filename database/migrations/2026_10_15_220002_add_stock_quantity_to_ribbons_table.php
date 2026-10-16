<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ribbons', function (Blueprint $table) {
            $table->unsignedInteger('stock_quantity')->default(0)->after('price');
        });

        // See the matching flowers migration — backfill existing rows so
        // they stay orderable until an admin sets a real number.
        DB::table('ribbons')->update(['stock_quantity' => 50]);
    }

    public function down(): void
    {
        Schema::table('ribbons', function (Blueprint $table) {
            $table->dropColumn('stock_quantity');
        });
    }
};
