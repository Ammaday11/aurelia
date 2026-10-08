<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shop Identity
    |--------------------------------------------------------------------------
    |
    | Fallback values used anywhere the database-backed Settings row is not
    | yet seeded (first boot) or unreachable. The admin "Settings" screen
    | writes to the `settings` table, which always takes precedence via the
    | setting() helper. Keep these as the last-resort defaults only.
    |
    */

    'name' => env('SHOP_NAME', 'Aurelia Boutonnière'),

    'tagline' => 'Flowers, artfully arranged.',

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The shop trades exclusively in Maldivian Rufiyaa. The ISO code is used
    | internally (database columns, exports, payment references) while the
    | Rufiyaa sign is what customers see. See app/Support/helpers.php for the
    | money() formatter and resources/views/components/price.blade.php for
    | the <x-price> component that wraps it for display.
    |
    | NOTE on the codepoint: U+E900 is a Private Use Area assignment, not a
    | real Unicode codepoint — there currently isn't one. The Currency
    | Symbols block (U+20A0-U+20CF) stops at U+20C0 (SOM SIGN); nothing has
    | been assigned for the Maldivian rufiyaa. Browsers refuse to substitute
    | ANY font glyph for a genuinely unassigned codepoint (verified: our
    | bundled RufiyaaSign webfont renders perfectly at this PUA codepoint
    | and fails identically at U+20C2, even for a placeholder glyph) — so
    | PUA is the only codepoint choice that actually renders. See
    | resources/fonts/build_rufiyaa_font.py for the full explanation and
    | how to update this if Unicode ever assigns a real one.
    |
    */

    'currency' => [
        'code' => 'MVR',
        'sign' => "\u{E900}", // Private Use Area placeholder for the (unassigned) Maldivian rufiyaa sign
        'decimals' => 2,
        'thousands_separator' => ',',
        'decimal_separator' => '.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */

    'order_number_prefix' => 'AUR',

    'statuses' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'preparing' => 'Preparing',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ],

    'payment_methods' => [
        'cod' => 'Cash on Delivery',
        'bank_transfer' => 'Bank Transfer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Catalogue
    |--------------------------------------------------------------------------
    */

    'products_per_page' => 12,

    'low_stock_threshold' => 5,

    /*
    |--------------------------------------------------------------------------
    | Default delivery time slots
    |--------------------------------------------------------------------------
    |
    | Overridable from the admin Settings screen (stored as JSON in the
    | settings table under the "delivery_time_slots" key).
    |
    */

    'default_delivery_time_slots' => [
        '09:00 - 11:00',
        '11:00 - 13:00',
        '13:00 - 15:00',
        '15:00 - 17:00',
        '17:00 - 19:00',
        '19:00 - 21:00',
    ],

    'default_delivery_fee' => 50.00,

    /*
    |--------------------------------------------------------------------------
    | Testimonials
    |--------------------------------------------------------------------------
    |
    | Shown on the home page. Static for now (no admin CRUD was requested
    | for testimonials) — edit this array to change them.
    |
    */

    'testimonials' => [
        [
            'name' => 'Aishath Nazeera',
            'location' => 'Malé',
            'quote' => 'The bouquet arrived looking even better than the photos. Aurelia made my sister\'s birthday so special!',
            'rating' => 5,
        ],
        [
            'name' => 'Hassan Rasheed',
            'location' => 'Hulhumalé',
            'quote' => 'Ordering was effortless and delivery was right on time for our anniversary. Will absolutely order again.',
            'rating' => 5,
        ],
        [
            'name' => 'Fathimath Shaaba',
            'location' => 'Malé',
            'quote' => 'Beautifully arranged, fresh flowers, and the gift note touch made it feel truly personal.',
            'rating' => 5,
        ],
    ],

];
