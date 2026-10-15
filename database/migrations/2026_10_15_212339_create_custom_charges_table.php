<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_charges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('amount', 8, 2);

            // is_automatic: applied to the total of every order placed while
            // this charge is active (like an admin-named extra delivery
            // fee). When false, the charge is only ever added to an order
            // when an admin manually attaches it from the order's detail
            // page (see Admin\OrderController::attachCharge()) — e.g. a
            // one-off "Rush Order Fee" that doesn't apply to every order.
            $table->boolean('is_automatic')->default(false);

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_charges');
    }
};
