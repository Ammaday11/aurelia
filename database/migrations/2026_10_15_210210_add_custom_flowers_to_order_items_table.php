<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Snapshot of the chosen flowers for a custom-bouquet line item
            // (product_id is null for these rows): [{flower_id, name,
            // unit_price, quantity}, ...]. Stored as JSON, like
            // product_title/unit_price, so an order's history stays
            // intact even if a flower is later renamed, repriced, or
            // removed from the catalogue.
            $table->json('custom_flowers')->nullable()->after('gift_message');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('custom_flowers');
        });
    }
};
