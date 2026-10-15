<?php

namespace Database\Seeders;

use App\Models\CustomCharge;
use Illuminate\Database\Seeder;

class CustomChargeSeeder extends Seeder
{
    public function run(): void
    {
        $charges = [
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
