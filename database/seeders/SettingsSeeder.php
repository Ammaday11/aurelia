<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::setMany([
            'shop_name' => 'Aurelia Boutonnière',
            'contact_email' => 'hello@aureliaboutonniere.test',
            'contact_phone' => '+960 777-1234',
            'whatsapp_number' => '+960 777-1234',
            'contact_address' => 'Chandhanee Magu, Malé, Maldives',
            'opening_hours' => 'Daily, 9:00am – 9:00pm',
            'social_facebook' => 'https://facebook.com/aureliaboutonniere',
            'social_instagram' => 'https://instagram.com/aureliaboutonniere',
            'social_tiktok' => 'https://tiktok.com/@aureliaboutonniere',

            // Delivery fee itself is seeded in CustomChargeSeeder (admin-
            // managed under Custom Charges, not here).
            'delivery_time_slots' => json_encode(config('shop.default_delivery_time_slots')),

            'bank_name' => 'Bank of Maldives',
            'bank_account_name' => 'Aurelia Boutonnière Pvt Ltd',
            'bank_account_number' => '7730-000-123456',

            'hero_tagline' => 'Every arrangement is crafted to order — fresh, elegant, and ready to brighten someone\'s day.',
            'about_intro' => 'Aurelia Boutonnière began with a simple belief: flowers should feel personal. From our studio we hand-tie every bouquet to order, pairing seasonal blooms with a little bit of care for every occasion — big or small.',
            'footer_tagline' => 'Hand-tied bouquets and thoughtful arrangements, delivered fresh across the Maldives.',
            'meta_description' => 'Fresh, hand-tied bouquets and flower arrangements in the Maldives, delivered with care. Order online from Aurelia Boutonnière.',
        ]);

        $this->command->info('Shop settings seeded.');
    }
}
