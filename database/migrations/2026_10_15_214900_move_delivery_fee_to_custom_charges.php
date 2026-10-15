<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Delivery fee moves from the Settings page to a regular admin-managed
 * CustomCharge (see App\Models\CustomCharge) — an automatic charge behaves
 * exactly like the old delivery_fee did (added to every order's total
 * while active), so it no longer needs its own special-cased Setting,
 * column, or admin form field. Any amount an admin had already configured
 * in Settings is carried over onto the new charge so this is a lossless
 * move, not a reset to the config default.
 *
 * orders.delivery_fee itself is left in place (not dropped) so already-
 * placed orders keep showing their original delivery fee — see
 * Order::recalculateTotal() and resources/views/components/order-totals.blade.php,
 * which only fall back to that column when it's non-zero. New orders
 * always write 0 there now; their delivery fee shows up as a normal
 * OrderCharge row instead (see OrderService::placeOrder()).
 */
return new class extends Migration
{
    public function up(): void
    {
        $existing = DB::table('settings')->where('key', 'delivery_fee')->value('value');
        $amount = $existing !== null ? (float) $existing : (float) config('shop.default_delivery_fee');

        DB::table('custom_charges')->updateOrInsert(
            ['name' => 'Delivery Fee'],
            [
                'amount' => $amount,
                'is_automatic' => true,
                'is_active' => true,
                'sort_order' => 0,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('settings')->where('key', 'delivery_fee')->delete();

        // Setting::get() caches every key for an hour (see Setting::all_cached());
        // writing straight to the table bypasses the model events that
        // normally invalidate it, so clear it explicitly.
        Cache::forget(Setting::CACHE_KEY);
    }

    public function down(): void
    {
        $amount = DB::table('custom_charges')->where('name', 'Delivery Fee')->value('amount');

        if ($amount !== null) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'delivery_fee'],
                ['value' => $amount, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        DB::table('custom_charges')->where('name', 'Delivery Fee')->delete();
        Cache::forget(Setting::CACHE_KEY);
    }
};
