<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomCharge;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Covers App\Models\CustomCharge's two modes (see OrderService):
 * - is_automatic: applied to the total of every order placed while active.
 * - manual (is_automatic = false): only ever applied when an admin attaches
 *   it to a specific order from that order's detail page.
 */
class CustomChargeTest extends TestCase
{
    use RefreshDatabase;

    protected function makeProduct(): Product
    {
        $category = Category::create(['name' => 'Bouquets', 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id,
            'title' => 'Test Bouquet',
            'price' => 100,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);
    }

    protected function checkoutPayload(): array
    {
        return [
            'name' => 'Jane Doe',
            'phone' => '960 777-1234',
            'email' => 'jane@example.com',
            'delivery_address' => '123 Test Street, Malé',
            'delivery_date' => now()->addDay()->toDateString(),
            'delivery_time_slot' => config('shop.default_delivery_time_slots')[0],
            'payment_method' => 'cod',
        ];
    }

    public function test_an_active_automatic_charge_is_added_to_every_order_total(): void
    {
        Mail::fake();

        CustomCharge::create(['name' => 'Service Charge', 'amount' => 25, 'is_automatic' => true, 'is_active' => true]);

        $product = $this->makeProduct();
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::first();
        $this->assertNotNull($order);

        // subtotal 100 + delivery_fee (config default) + 25 service charge
        $deliveryFee = (float) config('shop.default_delivery_fee');
        $this->assertEqualsWithDelta(100 + $deliveryFee + 25, (float) $order->total, 0.001);

        $charge = $order->charges->first();
        $this->assertSame('Service Charge', $charge->name);
        $this->assertTrue($charge->is_automatic);
    }

    public function test_an_inactive_automatic_charge_is_not_applied(): void
    {
        Mail::fake();

        CustomCharge::create(['name' => 'Service Charge', 'amount' => 25, 'is_automatic' => true, 'is_active' => false]);

        $product = $this->makeProduct();
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::first();
        $this->assertCount(0, $order->charges);

        $deliveryFee = (float) config('shop.default_delivery_fee');
        $this->assertEqualsWithDelta(100 + $deliveryFee, (float) $order->total, 0.001);
    }

    public function test_a_manual_non_automatic_charge_is_never_applied_at_checkout(): void
    {
        Mail::fake();

        CustomCharge::create(['name' => 'Rush Order Fee', 'amount' => 50, 'is_automatic' => false, 'is_active' => true]);

        $product = $this->makeProduct();
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::first();
        $this->assertCount(0, $order->charges);
    }

    public function test_admin_can_attach_and_detach_a_manual_charge_on_an_order(): void
    {
        Mail::fake();

        $charge = CustomCharge::create(['name' => 'Rush Order Fee', 'amount' => 50, 'is_automatic' => false, 'is_active' => true]);

        $product = $this->makeProduct();
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('checkout.store'), $this->checkoutPayload());
        $order = Order::first();

        $deliveryFee = (float) config('shop.default_delivery_fee');
        $this->assertEqualsWithDelta(100 + $deliveryFee, (float) $order->total, 0.001);

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.orders.charges.attach', $order), ['custom_charge_id' => $charge->id])
            ->assertRedirect();

        $order->refresh();
        $this->assertEqualsWithDelta(100 + $deliveryFee + 50, (float) $order->total, 0.001);
        $this->assertCount(1, $order->charges);

        $orderCharge = $order->charges->first();

        $this->actingAs($admin)
            ->delete(route('admin.orders.charges.detach', [$order, $orderCharge]))
            ->assertRedirect();

        $order->refresh();
        $this->assertEqualsWithDelta(100 + $deliveryFee, (float) $order->total, 0.001);
        $this->assertCount(0, $order->charges);
    }

    public function test_an_automatic_charge_cannot_be_attached_through_the_manual_attach_endpoint(): void
    {
        Mail::fake();

        // Already applies itself to every order — attaching it again here
        // would double it up.
        $automaticCharge = CustomCharge::create(['name' => 'Service Charge', 'amount' => 25, 'is_automatic' => true, 'is_active' => true]);

        $product = $this->makeProduct();
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('checkout.store'), $this->checkoutPayload());
        $order = Order::first();

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.orders.charges.attach', $order), ['custom_charge_id' => $automaticCharge->id])
            ->assertSessionHas('error');

        $order->refresh();
        // Still just the one automatic charge from checkout, not two.
        $this->assertCount(1, $order->charges);
    }

    public function test_admin_can_manage_custom_charges(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.custom-charges.store'), [
                'name' => 'Holiday Surcharge',
                'amount' => 30,
                'is_automatic' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.custom-charges.index'));

        $this->assertDatabaseHas('custom_charges', [
            'name' => 'Holiday Surcharge',
            'amount' => 30.00,
            'is_automatic' => true,
        ]);
    }
}
