<?php

namespace Tests\Feature;

use App\Facades\Cart;
use App\Mail\OrderConfirmation;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Covers the business-critical path of the app: adding to cart, placing a
 * guest order, and the server-side stock/overselling guard. See
 * App\Services\OrderService for the implementation under test.
 */
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function makeProduct(array $overrides = []): Product
    {
        $category = Category::create([
            'name' => 'Bouquets',
            'is_active' => true,
        ]);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'title' => 'Test Bouquet',
            'price' => 100,
            'stock_quantity' => 5,
            'is_active' => true,
        ], $overrides));
    }

    public function test_guest_can_add_a_product_to_the_cart_and_see_it(): void
    {
        $product = $this->makeProduct();

        $this->post(route('cart.add', $product), ['quantity' => 2])
            ->assertRedirect(route('cart.index'));

        $this->assertSame(2, Cart::count());
        $this->assertEqualsWithDelta(200.0, Cart::subtotal(), 0.001);
    }

    public function test_guest_can_place_an_order_and_stock_is_decremented(): void
    {
        Mail::fake();

        $product = $this->makeProduct(['stock_quantity' => 5]);
        $this->post(route('cart.add', $product), ['quantity' => 2]);

        $response = $this->post(route('checkout.store'), [
            'name' => 'Jane Doe',
            'phone' => '960 777-1234',
            'email' => 'jane@example.com',
            'delivery_address' => '123 Test Street, Malé',
            'delivery_date' => now()->addDay()->toDateString(),
            'delivery_time_slot' => config('shop.default_delivery_time_slots')[0],
            'payment_method' => 'cod',
        ]);

        $order = Order::first();

        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.confirmation', $order->order_number));

        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertEqualsWithDelta(200.0, (float) $order->subtotal, 0.001);
        $this->assertTrue(Cart::isEmpty());

        Mail::assertSent(OrderConfirmation::class);
    }

    public function test_checkout_blocks_overselling_beyond_available_stock(): void
    {
        Mail::fake();

        $product = $this->makeProduct(['stock_quantity' => 1]);
        $this->post(route('cart.add', $product), ['quantity' => 1]);

        // A second guest's cart (simulated directly) wants the same last unit.
        Cart::update(array_key_first(Cart::items()), 1);
        $product->decrement('stock_quantity', 1); // simulate another order winning the race

        $response = $this->post(route('checkout.store'), [
            'name' => 'Late Buyer',
            'phone' => '960 777-5678',
            'email' => 'late@example.com',
            'delivery_address' => '456 Test Street, Malé',
            'delivery_date' => now()->addDay()->toDateString(),
            'delivery_time_slot' => config('shop.default_delivery_time_slots')[0],
            'payment_method' => 'cod',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, Order::count());
    }
}
