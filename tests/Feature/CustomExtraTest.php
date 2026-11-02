<?php

namespace Tests\Feature;

use App\Facades\Cart;
use App\Models\Extra;
use App\Models\Flower;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Covers "Extras" (chocolates, money gifts, etc.) — optional add-ons a
 * customer can pick any number of on the custom bouquet builder, stock-
 * tracked and admin-managed the same way Flower/WrappingPaper/Ribbon are.
 * See CartService::addCustom()'s custom_extras handling and the
 * extraDemand aggregation in OrderService::placeOrder()/updateStatus().
 */
class CustomExtraTest extends TestCase
{
    use RefreshDatabase;

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

    protected function makeRose(): Flower
    {
        return Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'stock_quantity' => 100, 'is_active' => true]);
    }

    public function test_the_builder_page_lists_active_extras_and_marks_out_of_stock_ones(): void
    {
        $this->makeRose();
        Extra::create(['name' => 'Box of Chocolates', 'price' => 75, 'stock_quantity' => 10, 'is_active' => true]);
        Extra::create(['name' => 'Discontinued Extra', 'price' => 10, 'stock_quantity' => 10, 'is_active' => false]);
        Extra::create(['name' => 'Teddy Bear', 'price' => 125, 'stock_quantity' => 0, 'is_active' => true]);

        $this->get(route('custom-bouquet'))
            ->assertOk()
            ->assertSee('Box of Chocolates')
            ->assertDontSee('Discontinued Extra')
            ->assertSee('Teddy Bear')
            ->assertSee('Out of Stock');
    }

    public function test_guest_can_add_extras_to_a_custom_bouquet(): void
    {
        $rose = $this->makeRose();
        $chocolates = Extra::create(['name' => 'Box of Chocolates', 'price' => 75, 'stock_quantity' => 10, 'is_active' => true]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'extras' => [$chocolates->id => 2],
        ])->assertRedirect(route('cart.index'));

        // 3 * 35 (flowers) + 2 * 75 (extras) = 105 + 150 = 255
        $this->assertEqualsWithDelta(255.0, Cart::subtotal(), 0.001);

        $item = Cart::content()->first();
        $this->assertCount(1, $item->extras);
        $this->assertSame('Box of Chocolates', $item->extras->first()->extra->name);
        $this->assertSame(2, $item->extras->first()->quantity);
    }

    public function test_an_inactive_or_out_of_stock_extra_is_silently_dropped_from_a_new_selection(): void
    {
        $rose = $this->makeRose();
        $inactive = Extra::create(['name' => 'Discontinued Extra', 'price' => 50, 'stock_quantity' => 10, 'is_active' => false]);
        $outOfStock = Extra::create(['name' => 'Teddy Bear', 'price' => 125, 'stock_quantity' => 0, 'is_active' => true]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'extras' => [$inactive->id => 1, $outOfStock->id => 1],
        ])->assertRedirect(route('cart.index'));

        $item = Cart::content()->first();
        $this->assertCount(0, $item->extras);
        // Only the flowers' cost — neither extra was applied.
        $this->assertEqualsWithDelta(105.0, Cart::subtotal(), 0.001);
    }

    public function test_extras_are_snapshotted_on_the_order_and_checkout_rejects_a_deactivated_one(): void
    {
        Mail::fake();

        $rose = $this->makeRose();
        $chocolates = Extra::create(['name' => 'Box of Chocolates', 'price' => 75, 'stock_quantity' => 10, 'is_active' => true]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'extras' => [$chocolates->id => 2],
        ]);

        // Simulate the admin deactivating the extra after it was added.
        $chocolates->update(['is_active' => false]);

        $this->post(route('checkout.store'), $this->checkoutPayload())
            ->assertSessionHas('error');
        $this->assertSame(0, Order::count());

        $chocolates->update(['is_active' => true]);

        $this->post(route('checkout.store'), $this->checkoutPayload());

        $item = Order::first()->items->first();
        $this->assertCount(1, $item->custom_extras);
        $this->assertSame('Box of Chocolates', $item->custom_extras[0]['name']);
        $this->assertSame(2, $item->custom_extras[0]['quantity']);
        $this->assertEqualsWithDelta(255.0, (float) $item->unit_price, 0.001); // 3*35 + 2*75
    }

    public function test_checkout_decrements_extra_stock_by_total_demand_across_quantity_and_rows(): void
    {
        Mail::fake();

        $rose = $this->makeRose();
        $chocolates = Extra::create(['name' => 'Box of Chocolates', 'price' => 75, 'stock_quantity' => 10, 'is_active' => true]);

        // 2 chocolates per bouquet x 3 bouquets = 6 total.
        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'extras' => [$chocolates->id => 2],
            'quantity' => 3,
        ]);

        $this->post(route('checkout.store'), $this->checkoutPayload());

        $this->assertSame(1, Order::count());
        $this->assertSame(4, $chocolates->fresh()->stock_quantity); // 10 - 6
    }

    public function test_demand_for_the_same_extra_across_two_cart_rows_is_combined_before_checking_stock(): void
    {
        Mail::fake();

        $rose = $this->makeRose();
        // Exactly enough for one row's worth, not both.
        $chocolates = Extra::create(['name' => 'Box of Chocolates', 'price' => 75, 'stock_quantity' => 3, 'is_active' => true]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'extras' => [$chocolates->id => 2],
            'gift_message' => 'For mum',
        ]);
        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'extras' => [$chocolates->id => 2],
            'gift_message' => 'For the office',
        ]);

        $response = $this->post(route('checkout.store'), $this->checkoutPayload());

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Box of Chocolates', session('error'));
        $this->assertSame(0, Order::count());
        $this->assertSame(3, $chocolates->fresh()->stock_quantity);
    }

    public function test_cancelling_an_order_restocks_its_extras_and_reinstating_decrements_again(): void
    {
        Mail::fake();

        $rose = $this->makeRose();
        $chocolates = Extra::create(['name' => 'Box of Chocolates', 'price' => 75, 'stock_quantity' => 10, 'is_active' => true]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'extras' => [$chocolates->id => 2],
            'quantity' => 2,
        ]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::first();
        $this->assertSame(6, $chocolates->fresh()->stock_quantity); // 10 - 4

        $admin = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'cancelled']);
        $this->assertSame(10, $chocolates->fresh()->stock_quantity);

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'pending']);
        $this->assertSame(6, $chocolates->fresh()->stock_quantity);
    }

    public function test_admin_dashboard_lists_low_and_out_of_stock_extras(): void
    {
        Extra::create(['name' => 'Box of Chocolates', 'price' => 75, 'stock_quantity' => 3, 'is_active' => true]);
        Extra::create(['name' => 'Teddy Bear', 'price' => 125, 'stock_quantity' => 0, 'is_active' => true]);

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Box of Chocolates')
            ->assertSee('1 item out of stock');
    }

    public function test_admin_can_manage_extras(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.extras.store'), [
                'name' => 'Teddy Bear',
                'price' => 125,
                'stock_quantity' => 15,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.extras.index'));

        $this->assertDatabaseHas('extras', ['name' => 'Teddy Bear', 'price' => 125.00, 'stock_quantity' => 15]);
    }

    public function test_admin_extra_form_requires_stock_quantity(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.extras.store'), [
                'name' => 'Teddy Bear',
                'price' => 125,
            ])
            ->assertSessionHasErrors('stock_quantity');
    }

    public function test_guest_cannot_add_an_extra_without_any_flowers(): void
    {
        $chocolates = Extra::create(['name' => 'Box of Chocolates', 'price' => 75, 'stock_quantity' => 10, 'is_active' => true]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [],
            'extras' => [$chocolates->id => 1],
        ])->assertSessionHasErrors('flowers');

        $this->assertTrue(Cart::isEmpty());
    }
}
