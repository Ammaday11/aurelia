<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Snapshotted, same reasoning as product_title/unit_price —
            // a custom bouquet's wrapping paper/ribbon choice (if any),
            // independent of whether that WrappingPaper/Ribbon row still
            // exists by the time the order is viewed later.
            $table->string('wrapping_paper_name')->nullable()->after('custom_flowers');
            $table->decimal('wrapping_paper_price', 8, 2)->nullable()->after('wrapping_paper_name');
            $table->string('ribbon_name')->nullable()->after('wrapping_paper_price');
            $table->decimal('ribbon_price', 8, 2)->nullable()->after('ribbon_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['wrapping_paper_name', 'wrapping_paper_price', 'ribbon_name', 'ribbon_price']);
        });
    }
};
