<?php

namespace Tests\Feature;

use App\Facades\Cart;
use App\Mail\OrderConfirmation;
use App\Models\Flower;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Covers the custom-bouquet flow (/custom-bouquet -> cart.add-custom ->
 * checkout): a customer picks flower types/quantities instead of a fixed
 * Product. See CartService::addCustom() and the custom-item branch of
 * OrderService::placeOrder().
 */
class CustomBouquetTest extends TestCase
{
    use RefreshDatabase;

    protected function makeFlowers(): array
    {
        return [
            Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'is_active' => true]),
            Flower::create(['name' => 'Lily', 'price_per_stem' => 40, 'is_active' => true]),
        ];
    }

    public function test_the_builder_page_only_lists_active_flowers(): void
    {
        Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'is_active' => true]);
        Flower::create(['name' => 'Wilted Thing', 'price_per_stem' => 10, 'is_active' => false]);

        $this->get(route('custom-bouquet'))
            ->assertOk()
            ->assertSee('Rose')
            ->assertDontSee('Wilted Thing');
    }

    public function test_guest_can_add_a_custom_bouquet_to_the_cart(): void
    {
        [$rose, $lily] = $this->makeFlowers();

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 6, $lily->id => 2],
            'quantity' => 1,
            'gift_message' => 'For mum',
        ])->assertRedirect(route('cart.index'));

        $this->assertSame(1, Cart::count());
        // 6 x 35 + 2 x 40 = 210 + 80 = 290
        $this->assertEqualsWithDelta(290.0, Cart::subtotal(), 0.001);

        $content = Cart::content();
        $this->assertCount(1, $content);
        $this->assertTrue($content->first()->is_custom);
        $this->assertSame('For mum', $content->first()->gift_message);
    }

    public function test_adding_a_bouquet_with_no_flowers_selected_is_rejected(): void
    {
        $rose = Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'is_active' => true]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 0],
        ])->assertSessionHasErrors('flowers');

        $this->assertTrue(Cart::isEmpty());
    }

    public function test_an_inactive_flower_cannot_be_added_even_if_posted_directly(): void
    {
        $inactive = Flower::create(['name' => 'Out of Season', 'price_per_stem' => 50, 'is_active' => false]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$inactive->id => 3],
        ])->assertSessionHas('error');

        $this->assertTrue(Cart::isEmpty());
    }

    public function test_guest_can_check_out_a_custom_bouquet_and_it_is_snapshotted_on_the_order(): void
    {
        Mail::fake();

        [$rose, $lily] = $this->makeFlowers();

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 6, $lily->id => 2],
            'quantity' => 1,
        ]);

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

        $this->assertEqualsWithDelta(290.0, (float) $order->subtotal, 0.001);
        $this->assertTrue(Cart::isEmpty());

        $item = $order->items->first();
        $this->assertTrue($item->isCustom());
        $this->assertNull($item->product_id);
        $this->assertCount(2, $item->custom_flowers);
        $this->assertStringContainsString('Rose', $item->product_title);

        // Flowers have no stock to decrement — only confirm nothing broke.
        $this->assertSame(35.0, (float) $rose->fresh()->price_per_stem);

        Mail::assertSent(OrderConfirmation::class);
    }

    public function test_checkout_rejects_a_custom_bouquet_whose_flower_became_unavailable_after_adding(): void
    {
        Mail::fake();

        [$rose, $lily] = $this->makeFlowers();

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 6, $lily->id => 2],
        ]);

        // Simulate the admin deactivating the flower after it was added.
        $lily->update(['is_active' => false]);

        $response = $this->post(route('checkout.store'), [
            'name' => 'Jane Doe',
            'phone' => '960 777-1234',
            'email' => 'jane@example.com',
            'delivery_address' => '123 Test Street, Malé',
            'delivery_date' => now()->addDay()->toDateString(),
            'delivery_time_slot' => config('shop.default_delivery_time_slots')[0],
            'payment_method' => 'cod',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, Order::count());
    }

    public function test_admin_can_manage_flowers(): void
    {
        $admin = \App\Models\User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.flowers.store'), [
                'name' => 'Peony',
                'price_per_stem' => 45,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.flowers.index'));

        $this->assertDatabaseHas('flowers', ['name' => 'Peony', 'price_per_stem' => 45.00]);
    }
}
