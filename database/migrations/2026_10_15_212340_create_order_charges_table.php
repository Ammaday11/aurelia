<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // nullOnDelete, same reasoning as order_items.product_id: keep
            // order history intact even if the charge definition is later
            // removed from the catalogue — name/amount are snapshotted.
            $table->foreignId('custom_charge_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->decimal('amount', 8, 2);
            $table->boolean('is_automatic')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_charges');
    }
};
