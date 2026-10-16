<?php

/**
 * Globally available helper functions for Aurelia Boutonnière.
 *
 * Autoloaded via the "files" entry in composer.json so money() and
 * setting() work anywhere: controllers, Blade views, Mailables, and the
 * PDF invoice renderer, without an explicit `use` import.
 */

use App\Models\Setting;

if (! function_exists('money')) {
    /**
     * Format an amount as a Rufiyaa string, e.g. "[sign] 1,250.00" (the
     * literal Rufiyaa sign character, a thin space, then the amount with
     * thousands separators and 2 decimals).
     *
     * This is the ONE place amounts are formatted for display. Every price
     * shown on the storefront, cart, checkout, admin panel, emails, and
     * invoices goes through this function (directly, or via the <x-price>
     * Blade component which calls it).
     *
     * The Rufiyaa sign is emitted as a real UTF-8 character (not an HTML
     * entity) so copy/paste and screen readers get the genuine symbol. It
     * lives at a Private Use Area codepoint (see config/shop.php and
     * resources/fonts/build_rufiyaa_font.py for why — short version: there
     * is no real assigned Unicode codepoint for this sign yet). No system
     * font has a glyph there, so pair this output with the "price-rufiyaa"
     * CSS class, which loads the bundled RufiyaaSign webfont scoped to
     * that single code point via unicode-range.
     */
    function money(float|int|string|null $amount, bool $withSign = true): string
    {
        $config = config('shop.currency');
        $amount = (float) ($amount ?? 0);

        $formatted = number_format(
            $amount,
            $config['decimals'],
            $config['decimal_separator'],
            $config['thousands_separator']
        );

        if (! $withSign) {
            return $formatted;
        }

        // U+2009 THIN SPACE between the sign and the figure.
        return $config['sign']."\u{2009}".$formatted;
    }

    /**
     * Returns the raw sign amount without the number.
     */
    function rufiyaa_sign(): string
    {
        return config('shop.currency.sign');
    }
}

if (! function_exists('setting')) {
    /**
     * Read a value from the admin-managed Settings table, falling back to
     * $default (or null) when the key does not exist yet — e.g. before the
     * settings seeder has run. Results are cached by the Setting model.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        if (! class_exists(Setting::class)) {
            return $default;
        }

        return Setting::get($key, $default);
    }
}

if (! function_exists('order_status_label')) {
    /**
     * Human-readable label for an order status slug.
     */
    function order_status_label(string $status): string
    {
        return config('shop.statuses')[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}

if (! function_exists('payment_method_label')) {
    /**
     * Human-readable label for a payment method slug.
     */
    function payment_method_label(string $method): string
    {
        return config('shop.payment_methods')[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }
}

if (! function_exists('whatsapp_link')) {
    /**
     * A wa.me link for the admin-configured WhatsApp number (Settings >
     * Contact Information), or null if none is set — the one place that
     * builds this link, used by the storefront's floating WhatsApp button
     * (see <x-whatsapp-button>).
     *
     * wa.me wants digits only (country code included, no "+", spaces, or
     * dashes), so whatever format the admin typed the number in gets
     * stripped down here rather than forcing them to enter it a specific
     * way in the Settings form.
     */
    function whatsapp_link(?string $message = null): ?string
    {
        $number = preg_replace('/\D+/', '', (string) setting('whatsapp_number'));

        if (blank($number)) {
            return null;
        }

        $url = "https://wa.me/{$number}";

        return $message ? $url.'?text='.rawurlencode($message) : $url;
    }
}
