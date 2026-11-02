<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Same shape/role as custom_flowers: [{extra_id, name, unit_price,
            // quantity}, ...]. No separate extra_id FK column is needed (unlike
            // wrapping_paper_id/ribbon_id) because restocking reads extra_id
            // straight out of each JSON row, the same way it does for flowers.
            $table->json('custom_extras')->nullable()->after('ribbon_price');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('custom_extras');
        });
    }
};
