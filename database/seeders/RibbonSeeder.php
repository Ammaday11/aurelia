<?php

namespace Database\Seeders;

use App\Models\Ribbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class RibbonSeeder extends Seeder
{
    protected string $sourceDir;

    public function __construct()
    {
        $this->sourceDir = database_path('seeders/images/ribbons');
    }

    public function run(): void
    {
        $ribbons = [
            ['name' => 'Satin Gold Ribbon', 'price' => 10.00, 'stock_quantity' => 60, 'image' => 'satin-gold', 'sort_order' => 1],
            // Deliberately low — see FlowerSeeder's Carnation for why.
            ['name' => 'Organza Blush Ribbon', 'price' => 10.00, 'stock_quantity' => 4, 'image' => 'organza-blush', 'sort_order' => 2],
        ];

        foreach ($ribbons as $definition) {
            $imageBase = $definition['image'];
            unset($definition['image']);

            $ribbon = Ribbon::updateOrCreate(
                ['name' => $definition['name']],
                $definition + ['is_active' => true]
            );

            if (! $ribbon->image) {
                $path = $this->copySeedImage($imageBase);

                if ($path) {
                    $ribbon->update(['image' => $path]);
                }
            }
        }

        $this->command->info('Ribbons seeded: '.count($ribbons));
    }

    protected function copySeedImage(string $imageBase): ?string
    {
        $source = "{$this->sourceDir}/{$imageBase}.png";

        if (! is_file($source)) {
            $this->command->warn("Seed image missing: {$imageBase}");

            return null;
        }

        $path = "ribbons/{$imageBase}-".uniqid().'.png';
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }
}
