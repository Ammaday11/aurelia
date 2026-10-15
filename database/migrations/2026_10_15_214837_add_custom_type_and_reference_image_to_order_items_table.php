<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // What kind of custom piece this is — a full bouquet or a
            // single boutonnière (the shop's namesake) — set on the
            // /custom-bouquet builder and used to generate the item's
            // title ("Custom Bouquet (...)" vs "Custom Boutonnière (...)").
            // Null for ordinary (non-custom) product lines.
            $table->string('custom_type')->nullable()->after('ribbon_price');

            // An optional reference/inspiration photo the customer
            // uploaded on the builder, so the florist knows the style
            // they're going for. Uploaded (and stored) at add-to-cart
            // time, not checkout — see CartService::addCustom() — because
            // the cart itself is just session data and can't hold an
            // actual file across requests.
            $table->string('reference_image_path')->nullable()->after('custom_type');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['custom_type', 'reference_image_path']);
        });
    }
};
