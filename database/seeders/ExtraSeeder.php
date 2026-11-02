<?php

namespace Database\Seeders;

use App\Models\Extra;
use Illuminate\Database\Seeder;

class ExtraSeeder extends Seeder
{
    public function run(): void
    {
        $extras = [
            ['name' => 'Box of Chocolates', 'price' => 75.00, 'stock_quantity' => 25, 'sort_order' => 1],
            ['name' => 'Money Gift', 'price' => 0.00, 'stock_quantity' => 50, 'sort_order' => 2],
            ['name' => 'Teddy Bear', 'price' => 125.00, 'stock_quantity' => 15, 'sort_order' => 3],
        ];

        foreach ($extras as $definition) {
            Extra::updateOrCreate(
                ['name' => $definition['name']],
                $definition + ['is_active' => true]
            );
        }

        $this->command->info('Extras seeded: '.count($extras).' (no seed photos yet — upload one for each in Admin → Extras).');
    }
}
