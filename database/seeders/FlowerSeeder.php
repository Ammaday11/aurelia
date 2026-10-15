<?php

namespace Database\Seeders;

use App\Models\Flower;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class FlowerSeeder extends Seeder
{
    /**
     * Source directory for the generated placeholder flower illustrations
     * (see database/seeders/images/flowers — each "<name>.png"). These
     * stand in for real flower photography until the shop uploads its own
     * via the admin panel (Flowers > edit > Photo).
     */
    protected string $sourceDir;

    public function __construct()
    {
        $this->sourceDir = database_path('seeders/images/flowers');
    }

    public function run(): void
    {
        $flowers = [
            ['name' => 'Rose', 'price_per_stem' => 35.00, 'image' => 'rose', 'sort_order' => 1],
            ['name' => 'Lily', 'price_per_stem' => 40.00, 'image' => 'lily', 'sort_order' => 2],
            ['name' => 'Tulip', 'price_per_stem' => 30.00, 'image' => 'tulip', 'sort_order' => 3],
            ['name' => 'Sunflower', 'price_per_stem' => 32.00, 'image' => 'sunflower', 'sort_order' => 4],
            ['name' => 'Carnation', 'price_per_stem' => 20.00, 'image' => 'carnation', 'sort_order' => 5],
            ['name' => "Baby's Breath", 'price_per_stem' => 15.00, 'image' => 'babys-breath', 'sort_order' => 6],
        ];

        foreach ($flowers as $definition) {
            $imageBase = $definition['image'];
            unset($definition['image']);

            $flower = Flower::updateOrCreate(
                ['name' => $definition['name']],
                $definition + ['is_active' => true]
            );

            if (! $flower->image) {
                $path = $this->copySeedImage($flower->id, $imageBase);

                if ($path) {
                    $flower->update(['image' => $path]);
                }
            }
        }

        $this->command->info('Flowers seeded: '.count($flowers));
    }

    protected function copySeedImage(int $flowerId, string $imageBase): ?string
    {
        $source = "{$this->sourceDir}/{$imageBase}.png";

        if (! is_file($source)) {
            $this->command->warn("Seed image missing: {$imageBase}");

            return null;
        }

        $path = "flowers/{$imageBase}-".uniqid().'.png';
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }
}
