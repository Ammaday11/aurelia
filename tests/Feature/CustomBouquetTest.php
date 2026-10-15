<?php

namespace Tests\Feature;

use App\Facades\Cart;
use App\Mail\OrderConfirmation;
use App\Models\Flower;
use App\Models\Order;
use App\Models\Ribbon;
use App\Models\WrappingPaper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

    public function test_guest_can_add_wrapping_paper_and_ribbon_to_a_custom_bouquet(): void
    {
        [$rose, $lily] = $this->makeFlowers();
        $wrap = WrappingPaper::create(['name' => 'Kraft Paper', 'price' => 20, 'is_active' => true]);
        $ribbon = Ribbon::create(['name' => 'Satin Ribbon', 'price' => 10, 'is_active' => true]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 6, $lily->id => 2],
            'wrapping_paper_id' => $wrap->id,
            'ribbon_id' => $ribbon->id,
        ])->assertRedirect(route('cart.index'));

        // 290 (flowers) + 20 (wrap) + 10 (ribbon) = 320
        $this->assertEqualsWithDelta(320.0, Cart::subtotal(), 0.001);

        $item = Cart::content()->first();
        $this->assertSame('Kraft Paper', $item->wrappingPaper->name);
        $this->assertSame('Satin Ribbon', $item->ribbon->name);
    }

    public function test_an_inactive_wrapping_paper_is_silently_dropped_from_a_new_selection(): void
    {
        [$rose] = $this->makeFlowers();
        $inactiveWrap = WrappingPaper::create(['name' => 'Discontinued Paper', 'price' => 20, 'is_active' => false]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'wrapping_paper_id' => $inactiveWrap->id,
        ])->assertRedirect(route('cart.index'));

        $item = Cart::content()->first();
        $this->assertNull($item->wrappingPaper);
        // Only the flowers' cost — the inactive wrap was never applied.
        $this->assertEqualsWithDelta(105.0, Cart::subtotal(), 0.001);
    }

    public function test_wrapping_and_ribbon_are_snapshotted_on_the_order_and_checkout_rejects_a_deactivated_one(): void
    {
        Mail::fake();

        [$rose] = $this->makeFlowers();
        $wrap = WrappingPaper::create(['name' => 'Kraft Paper', 'price' => 20, 'is_active' => true]);
        $ribbon = Ribbon::create(['name' => 'Satin Ribbon', 'price' => 10, 'is_active' => true]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'wrapping_paper_id' => $wrap->id,
            'ribbon_id' => $ribbon->id,
        ]);

        $checkoutPayload = [
            'name' => 'Jane Doe',
            'phone' => '960 777-1234',
            'email' => 'jane@example.com',
            'delivery_address' => '123 Test Street, Malé',
            'delivery_date' => now()->addDay()->toDateString(),
            'delivery_time_slot' => config('shop.default_delivery_time_slots')[0],
            'payment_method' => 'cod',
        ];

        // Simulate the admin deactivating the ribbon after it was added.
        $ribbon->update(['is_active' => false]);

        $this->post(route('checkout.store'), $checkoutPayload)
            ->assertSessionHas('error');
        $this->assertSame(0, Order::count());

        // Re-activate and the same cart now checks out fine, with both
        // choices snapshotted onto the order item.
        $ribbon->update(['is_active' => true]);

        $this->post(route('checkout.store'), $checkoutPayload);

        $item = Order::first()->items->first();
        $this->assertSame('Kraft Paper', $item->wrapping_paper_name);
        $this->assertSame('Satin Ribbon', $item->ribbon_name);
        $this->assertEqualsWithDelta(135.0, (float) $item->unit_price, 0.001); // 3*35 + 20 + 10
    }

    public function test_a_custom_order_defaults_to_bouquet_and_can_be_a_boutonniere(): void
    {
        [$rose] = $this->makeFlowers();

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
        ])->assertRedirect(route('cart.index'));

        $item = Cart::content()->first();
        $this->assertSame('bouquet', $item->type);
        $this->assertStringStartsWith('Custom Bouquet (', $item->title);

        Cart::clear();

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 1],
            'type' => 'boutonniere',
        ])->assertRedirect(route('cart.index'));

        $item = Cart::content()->first();
        $this->assertSame('boutonniere', $item->type);
        $this->assertStringStartsWith('Custom Boutonnière (', $item->title);
    }

    public function test_an_identical_bouquet_and_boutonniere_selection_do_not_merge_in_the_cart(): void
    {
        [$rose] = $this->makeFlowers();

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'type' => 'bouquet',
        ]);

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'type' => 'boutonniere',
        ]);

        $this->assertCount(2, Cart::content());
    }

    public function test_guest_can_upload_a_reference_image_and_it_persists_through_checkout(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$rose] = $this->makeFlowers();

        $this->post(route('cart.add-custom'), [
            'flowers' => [$rose->id => 3],
            'type' => 'boutonniere',
            'reference_image' => UploadedFile::fake()->image('inspiration.jpg'),
        ])->assertRedirect(route('cart.index'));

        $cartItem = Cart::content()->first();
        $this->assertNotNull($cartItem->referenceImageUrl);
        $this->assertStringContainsString('/storage/', $cartItem->referenceImageUrl);

        $this->post(route('checkout.store'), [
            'name' => 'Jane Doe',
            'phone' => '960 777-1234',
            'email' => 'jane@example.com',
            'delivery_address' => '123 Test Street, Malé',
            'delivery_date' => now()->addDay()->toDateString(),
            'delivery_time_slot' => config('shop.default_delivery_time_slots')[0],
            'payment_method' => 'cod',
        ]);

        $order = Order::first();
        $item = $order->items->first();

        $this->assertSame('boutonniere', $item->custom_type);
        $this->assertSame('Custom Boutonnière', $item->customTypeLabel());
        $this->assertNotNull($item->reference_image_path);
        Storage::disk('public')->assertExists($item->reference_image_path);
        $this->assertStringContainsString('/storage/', $item->referenceImageUrl());
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
