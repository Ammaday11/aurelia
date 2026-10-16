<?php

namespace Tests\Feature;

use App\Facades\Cart;
use App\Mail\OrderConfirmation;
use App\Models\Flower;
use App\Models\Order;
use App\Models\Ribbon;
use App\Models\User;
use App\Models\WrappingPaper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Covers stock tracking for Flower/WrappingPaper/Ribbon — the same
 * stock_quantity/InsufficientStockException mechanism Product already
 * has, applied to custom bouquet/boutonnière ingredients so a customer
 * can't build an order out of stems the shop doesn't have on hand. See
 * the demand-aggregation step in OrderService::placeOrder() and the
 * restock/decrement logic in OrderService::updateStatus().
 */
class CustomOrderStockTest extends TestCase
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

    public function test_a_flower_with_zero_stock_cannot_be_added_to_a_custom_order(): void
    {
        $outOfStock = Flower::create(['name' => 'Peony', 'price_per_stem' => 50, 'stock_quantity' => 0, 'is_active' => true]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$outOfStock->id => 3],
        ])->assertSessionHas('error');

        $this->assertTrue(Cart::isEmpty());
    }

    public function test_the_builder_page_marks_an_out_of_stock_flower(): void
    {
        Flower::create(['name' => 'Peony', 'price_per_stem' => 50, 'stock_quantity' => 0, 'is_active' => true]);

        $this->get(route('custom-bouquet'))
            ->assertOk()
            ->assertSee('Peony')
            ->assertSee('Out of Stock');
    }

    public function test_an_out_of_stock_wrapping_paper_and_ribbon_are_silently_dropped(): void
    {
        $rose = Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'stock_quantity' => 100, 'is_active' => true]);
        $wrap = WrappingPaper::create(['name' => 'Kraft Paper', 'price' => 20, 'stock_quantity' => 0, 'is_active' => true]);
        $ribbon = Ribbon::create(['name' => 'Satin Ribbon', 'price' => 10, 'stock_quantity' => 0, 'is_active' => true]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'wrapping_paper_id' => $wrap->id,
            'ribbon_id' => $ribbon->id,
        ])->assertRedirect(route('cart.index'));

        $item = Cart::content()->first();
        $this->assertNull($item->wrappingPaper);
        $this->assertNull($item->ribbon);
    }

    public function test_checkout_rejects_a_custom_order_that_exceeds_available_flower_stock(): void
    {
        Mail::fake();

        $rose = Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'stock_quantity' => 5, 'is_active' => true]);

        // 3 stems per bouquet x 2 bouquets = 6 stems needed, only 5 in stock.
        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'quantity' => 2,
        ]);

        $response = $this->post(route('checkout.store'), $this->checkoutPayload());

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Rose', session('error'));
        $this->assertSame(0, Order::count());
        $this->assertSame(5, $rose->fresh()->stock_quantity);
    }

    public function test_checkout_decrements_flower_wrap_and_ribbon_stock_by_total_demand(): void
    {
        Mail::fake();

        $rose = Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'stock_quantity' => 20, 'is_active' => true]);
        $wrap = WrappingPaper::create(['name' => 'Kraft Paper', 'price' => 20, 'stock_quantity' => 5, 'is_active' => true]);
        $ribbon = Ribbon::create(['name' => 'Satin Ribbon', 'price' => 10, 'stock_quantity' => 5, 'is_active' => true]);

        // 3 stems x 3 bouquets = 9 stems; wrap/ribbon: 1 each x 3 bouquets = 3 each.
        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'wrapping_paper_id' => $wrap->id,
            'ribbon_id' => $ribbon->id,
            'quantity' => 3,
        ]);

        $this->post(route('checkout.store'), $this->checkoutPayload());

        $this->assertSame(1, Order::count());
        $this->assertSame(11, $rose->fresh()->stock_quantity);
        $this->assertSame(2, $wrap->fresh()->stock_quantity);
        $this->assertSame(2, $ribbon->fresh()->stock_quantity);
    }

    public function test_demand_for_the_same_flower_across_two_cart_rows_is_combined_before_checking_stock(): void
    {
        Mail::fake();

        // Exactly enough for one bouquet of each, not both.
        $rose = Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'stock_quantity' => 5, 'is_active' => true]);

        // Two separate custom bouquets (different gift messages keep them
        // as separate cart rows), each wanting 3 Roses — 6 total, only 5
        // in stock. Checking each row independently against the same
        // unmodified stock_quantity would wrongly let both pass.
        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'gift_message' => 'For mum',
        ]);
        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'gift_message' => 'For the office',
        ]);

        $this->assertCount(2, Cart::content());

        $response = $this->post(route('checkout.store'), $this->checkoutPayload());

        $response->assertSessionHas('error');
        $this->assertSame(0, Order::count());
        $this->assertSame(5, $rose->fresh()->stock_quantity);
    }

    public function test_cancelling_an_order_restocks_its_flowers_wrap_and_ribbon_and_reinstating_decrements_again(): void
    {
        Mail::fake();

        $rose = Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'stock_quantity' => 20, 'is_active' => true]);
        $wrap = WrappingPaper::create(['name' => 'Kraft Paper', 'price' => 20, 'stock_quantity' => 5, 'is_active' => true]);
        $ribbon = Ribbon::create(['name' => 'Satin Ribbon', 'price' => 10, 'stock_quantity' => 5, 'is_active' => true]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'wrapping_paper_id' => $wrap->id,
            'ribbon_id' => $ribbon->id,
            'quantity' => 2,
        ]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::first();
        $this->assertSame(14, $rose->fresh()->stock_quantity); // 20 - 6
        $this->assertSame(3, $wrap->fresh()->stock_quantity); // 5 - 2
        $this->assertSame(3, $ribbon->fresh()->stock_quantity); // 5 - 2

        $admin = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'cancelled']);

        $this->assertSame(20, $rose->fresh()->stock_quantity);
        $this->assertSame(5, $wrap->fresh()->stock_quantity);
        $this->assertSame(5, $ribbon->fresh()->stock_quantity);

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'pending']);

        $this->assertSame(14, $rose->fresh()->stock_quantity);
        $this->assertSame(3, $wrap->fresh()->stock_quantity);
        $this->assertSame(3, $ribbon->fresh()->stock_quantity);
    }

    public function test_admin_dashboard_lists_low_and_out_of_stock_flowers_wrap_and_ribbons(): void
    {
        Flower::create(['name' => 'Carnation', 'price_per_stem' => 20, 'stock_quantity' => 3, 'is_active' => true]);
        WrappingPaper::create(['name' => 'Blush Tissue', 'price' => 15, 'stock_quantity' => 0, 'is_active' => true]);
        Ribbon::create(['name' => 'Organza Ribbon', 'price' => 10, 'stock_quantity' => 2, 'is_active' => true]);

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Carnation')
            ->assertSee('Organza Ribbon')
            ->assertSee('1 item out of stock');
    }

    public function test_admin_flower_form_requires_stock_quantity(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.flowers.store'), [
                'name' => 'Peony',
                'price_per_stem' => 45,
            ])
            ->assertSessionHasErrors('stock_quantity');
    }
}
