<?php

namespace Database\Seeders;

use App\Models\WrappingPaper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class WrappingPaperSeeder extends Seeder
{
    protected string $sourceDir;

    public function __construct()
    {
        $this->sourceDir = database_path('seeders/images/wrapping-papers');
    }

    public function run(): void
    {
        $papers = [
            ['name' => 'Kraft Paper', 'price' => 20.00, 'stock_quantity' => 40, 'image' => 'kraft-paper', 'sort_order' => 1],
            ['name' => 'White Tissue', 'price' => 15.00, 'stock_quantity' => 30, 'image' => 'white-tissue', 'sort_order' => 2],
            // Deliberately low — see FlowerSeeder's Carnation for why.
            ['name' => 'Blush Tissue', 'price' => 15.00, 'stock_quantity' => 3, 'image' => 'blush-tissue', 'sort_order' => 3],
        ];

        foreach ($papers as $definition) {
            $imageBase = $definition['image'];
            unset($definition['image']);

            $paper = WrappingPaper::updateOrCreate(
                ['name' => $definition['name']],
                $definition + ['is_active' => true]
            );

            if (! $paper->image) {
                $path = $this->copySeedImage($imageBase);

                if ($path) {
                    $paper->update(['image' => $path]);
                }
            }
        }

        $this->command->info('Wrapping papers seeded: '.count($papers));
    }

    protected function copySeedImage(string $imageBase): ?string
    {
        $source = "{$this->sourceDir}/{$imageBase}.png";

        if (! is_file($source)) {
            $this->command->warn("Seed image missing: {$imageBase}");

            return null;
        }

        $path = "wrapping-papers/{$imageBase}-".uniqid().'.png';
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }
}
