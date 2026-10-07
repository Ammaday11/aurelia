<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            // Guest checkout contact & delivery details.
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('delivery_address');
            $table->date('delivery_date');
            $table->string('delivery_time_slot');
            $table->text('gift_message')->nullable();
            $table->text('special_notes')->nullable();

            // Payment.
            $table->enum('payment_method', ['cod', 'bank_transfer']);
            $table->string('payment_receipt_path')->nullable();

            // Workflow status.
            $table->enum('status', [
                'pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled',
            ])->default('pending');

            // Totals are always calculated server-side and stored as the
            // authoritative record of what was charged at order time.
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('MVR');

            $table->ipAddress('placed_from_ip')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
