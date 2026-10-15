<?php

namespace Database\Seeders;

use App\Models\CustomCharge;
use Illuminate\Database\Seeder;

class CustomChargeSeeder extends Seeder
{
    public function run(): void
    {
        $charges = [
            // Delivery fee lives here, not in Settings — it's just an
            // automatic charge applied to every order's total like any
            // other (see App\Models\CustomCharge), seeded active so a
            // fresh install still charges for delivery out of the box.
            // Sort first so it reads naturally above the optional extras.
            [
                'name' => 'Delivery Fee',
                'amount' => (float) config('shop.default_delivery_fee'),
                'is_automatic' => true,
                'is_active' => true,
                'sort_order' => 0,
            ],
            // Seeded inactive — an "automatic" charge applies to every
            // order placed while active, so this stays off until an admin
            // deliberately turns it on (Settings aren't the right place
            // for "a fee just started appearing on checkout" surprises).
            [
                'name' => 'Service Charge',
                'amount' => 25.00,
                'is_automatic' => true,
                'is_active' => false,
                'sort_order' => 1,
            ],
            // Seeded active — harmless until an admin manually attaches it
            // to a specific order from that order's detail page.
            [
                'name' => 'Rush Order Fee',
                'amount' => 100.00,
                'is_automatic' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($charges as $definition) {
            CustomCharge::updateOrCreate(['name' => $definition['name']], $definition);
        }

        $this->command->info('Custom charges seeded: '.count($charges));
    }
}
