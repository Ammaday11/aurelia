<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the floating WhatsApp button (<x-whatsapp-button>, included
 * site-wide by resources/views/layouts/storefront.blade.php) and the
 * admin-configurable number that drives it — see whatsapp_link() in
 * app/Support/helpers.php, the one place the wa.me URL is built.
 */
class WhatsAppButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_button_is_hidden_when_no_number_is_configured(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('wa.me', false)
            ->assertDontSee('Chat with us on WhatsApp');
    }

    public function test_the_button_links_to_a_sanitized_wa_me_url_once_configured(): void
    {
        Setting::set('whatsapp_number', '+960 777-1234');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('https://wa.me/9607771234', false);
        $response->assertDontSee('+960 777-1234', false);
    }

    public function test_whatsapp_link_helper_returns_null_when_unset(): void
    {
        $this->assertNull(whatsapp_link());
    }

    public function test_whatsapp_link_helper_sanitizes_and_appends_a_message(): void
    {
        Setting::set('whatsapp_number', '(960) 777-1234');

        $this->assertSame(
            'https://wa.me/9607771234?text=Hi%21',
            whatsapp_link('Hi!')
        );
    }

    public function test_admin_can_set_the_whatsapp_number_from_settings(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'shop_name' => 'Aurelia Boutonnière',
                'whatsapp_number' => '+960 999-8888',
                'delivery_time_slots' => ['09:00 - 11:00'],
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('+960 999-8888', Setting::get('whatsapp_number'));
    }

    public function test_an_invalid_whatsapp_number_is_rejected(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'shop_name' => 'Aurelia Boutonnière',
                'whatsapp_number' => 'not-a-number!!',
                'delivery_time_slots' => ['09:00 - 11:00'],
            ])
            ->assertSessionHasErrors('whatsapp_number');
    }
}
