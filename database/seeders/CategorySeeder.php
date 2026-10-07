<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Bouquets',
                'description' => 'Hand-tied, ready-to-gift bouquets for every occasion.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Roses',
                'description' => 'Classic and contemporary rose arrangements, from a single stem to a full dozen.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Arrangements',
                'description' => 'Lush vase and box arrangements designed to make a statement at home or the office.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Wedding Flowers',
                'description' => 'Bridal bouquets, centerpieces, and ceremony flowers for your big day.',
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category + ['is_active' => true]);
        }

        $this->command->info('Categories seeded: '.count($categories));
    }
}
