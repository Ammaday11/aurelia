<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * wrapping_paper_name/ribbon_name are a name-only snapshot — fine for
 * display, but restocking on order cancellation (see
 * OrderService::updateStatus()) needs to know exactly which
 * WrappingPaper/Ribbon row to credit back, which a name can't guarantee
 * (it could be renamed, or two rows could share a name). These columns
 * store that id alongside the snapshot, the same nullOnDelete pattern as
 * order_items.product_id — order history survives the row being deleted,
 * it just can no longer be restocked.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('wrapping_paper_id')->nullable()->after('custom_flowers')->constrained()->nullOnDelete();
            $table->foreignId('ribbon_id')->nullable()->after('wrapping_paper_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wrapping_paper_id');
            $table->dropConstrainedForeignId('ribbon_id');
        });
    }
};
