<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    /**
     * Source directory for the generated placeholder bouquet illustrations
     * (see database/seeders/images/products — each "<name>.png" has a
     * matching "<name>-thumb.png"). These stand in for real product
     * photography until the shop uploads its own via the admin panel.
     */
    protected string $sourceDir;

    public function __construct()
    {
        $this->sourceDir = database_path('seeders/images/products');
    }

    public function run(): void
    {
        $products = [
            // Bouquets
            [
                'category' => 'Bouquets',
                'title' => 'Meadow Breeze Bouquet',
                'price' => 450.00,
                'stock' => 18,
                'featured' => false,
                'images' => ['daisy-meadow-01'],
                'description' => '<p>A cheerful, garden-fresh bouquet of daisies and mixed blooms in soft sandy tones — perfect for brightening up any room.</p><p>Hand-tied by our florists and wrapped in natural kraft paper.</p>',
            ],
            [
                'category' => 'Bouquets',
                'title' => 'Garden Whisper Bouquet',
                'price' => 480.00,
                'sale_price' => 420.00,
                'stock' => 4,
                'featured' => true,
                'images' => ['daisy-meadow-02'],
                'description' => '<p>A delicate mix of daisies and seasonal fillers, arranged with a loose, just-picked-from-the-garden feel.</p><p>A lovely everyday gesture or a gentle get-well gift.</p>',
            ],
            [
                'category' => 'Bouquets',
                'title' => 'Wild Harvest Bouquet',
                'price' => 520.00,
                'stock' => 15,
                'featured' => false,
                'images' => ['mixed-garden-01'],
                'description' => '<p>An abundant, textural bouquet combining roses, daisies, and soft puff blooms for a wonderfully full silhouette.</p>',
            ],

            // Roses
            [
                'category' => 'Roses',
                'title' => 'Blush Rose Embrace',
                'price' => 650.00,
                'stock' => 22,
                'featured' => true,
                'images' => ['rose-blush-01', 'rose-blush-02'],
                'description' => '<p>Soft blush roses layered for a romantic, timeless look — our most requested arrangement for anniversaries.</p><p>Available with an optional handwritten gift card.</p>',
            ],
            [
                'category' => 'Roses',
                'title' => 'Classic Dozen Roses',
                'price' => 700.00,
                'stock' => 20,
                'featured' => false,
                'images' => ['rose-blush-02'],
                'description' => '<p>The timeless gesture: a dozen hand-selected roses, finished with a satin wrap.</p>',
            ],
            [
                'category' => 'Roses',
                'title' => 'Mocha Rose Elegance',
                'price' => 680.00,
                'sale_price' => 599.00,
                'stock' => 3,
                'featured' => false,
                'images' => ['rose-mocha-01'],
                'description' => '<p>Warm mocha-toned roses paired with blush accents for an elegant, understated statement piece.</p>',
            ],

            // Arrangements
            [
                'category' => 'Arrangements',
                'title' => 'Cloud Nine Peonies',
                'price' => 750.00,
                'stock' => 12,
                'featured' => true,
                'images' => ['peony-cloud-01', 'peony-cloud-02'],
                'description' => '<p>Voluminous, cloud-like peony blooms arranged for maximum impact — a showstopper for any celebration.</p>',
            ],
            [
                'category' => 'Arrangements',
                'title' => 'Soft Peony Delight',
                'price' => 720.00,
                'stock' => 10,
                'featured' => false,
                'images' => ['peony-cloud-02'],
                'description' => '<p>A gentler take on our peony arrangement, mixing soft neutrals with a touch of rose gold.</p>',
            ],
            [
                'category' => 'Arrangements',
                'title' => 'Harvest Garden Vase',
                'price' => 560.00,
                'stock' => 0,
                'featured' => false,
                'images' => ['mixed-garden-02'],
                'description' => '<p>A generous vase arrangement combining roses, daisies, and foliage for a lush, natural display.</p>',
            ],

            // Wedding Flowers
            [
                'category' => 'Wedding Flowers',
                'title' => 'Ivory Bridal Bouquet',
                'price' => 950.00,
                'stock' => 8,
                'featured' => true,
                'images' => ['wedding-white-01', 'wedding-white-02'],
                'description' => '<p>An heirloom-style bridal bouquet in soft ivory and blush tones, finished with trailing ribbon.</p><p>Custom sizing and matching bridesmaid bouquets available — contact us to discuss your wedding florals.</p>',
            ],
            [
                'category' => 'Wedding Flowers',
                'title' => 'Eternal Vow Centerpiece',
                'price' => 880.00,
                'stock' => 9,
                'featured' => false,
                'images' => ['wedding-white-02'],
                'description' => '<p>A low, romantic centerpiece designed to complement any tablescape without blocking the conversation.</p>',
            ],
            [
                'category' => 'Wedding Flowers',
                'title' => 'Golden Hour Celebration',
                'price' => 610.00,
                'sale_price' => 549.00,
                'stock' => 14,
                'featured' => false,
                'images' => ['sunset-bloom-01'],
                'description' => '<p>Warm, sunset-inspired tones for an engagement party, anniversary, or any golden-hour celebration.</p>',
            ],
        ];

        $categories = Category::query()->pluck('id', 'name');

        foreach ($products as $definition) {
            $categoryId = $categories[$definition['category']] ?? null;

            if (! $categoryId) {
                $this->command->warn("Skipping \"{$definition['title']}\" — category \"{$definition['category']}\" not found.");

                continue;
            }

            $product = Product::updateOrCreate(
                ['title' => $definition['title']],
                [
                    'category_id' => $categoryId,
                    'description' => $definition['description'],
                    'price' => $definition['price'],
                    'sale_price' => $definition['sale_price'] ?? null,
                    'stock_quantity' => $definition['stock'],
                    'is_featured' => $definition['featured'],
                    'is_active' => true,
                ]
            );

            // Re-seeding (e.g. db:seed run twice) shouldn't duplicate images.
            if ($product->images()->doesntExist()) {
                foreach ($definition['images'] as $sort => $imageBase) {
                    $stored = $this->copySeedImage($product->id, $imageBase);

                    if ($stored) {
                        $product->images()->create([
                            'path' => $stored['path'],
                            'thumb_path' => $stored['thumb_path'],
                            'sort_order' => $sort,
                        ]);
                    }
                }
            }
        }

        $this->command->info('Products seeded: '.count($products));
    }

    /**
     * Copies a pre-generated "<imageBase>.png" / "<imageBase>-thumb.png"
     * pair into storage/app/public/products/{productId}/, returning the
     * disk-relative paths (same shape ImageService::storeWithThumbnail()
     * produces for admin uploads).
     *
     * @return array{path: string, thumb_path: string}|null
     */
    protected function copySeedImage(int $productId, string $imageBase): ?array
    {
        $fullSource = "{$this->sourceDir}/{$imageBase}.png";
        $thumbSource = "{$this->sourceDir}/{$imageBase}-thumb.png";

        if (! is_file($fullSource) || ! is_file($thumbSource)) {
            $this->command->warn("Seed image missing: {$imageBase}");

            return null;
        }

        $directory = "products/{$productId}";
        $filename = $imageBase.'-'.uniqid().'.png';
        $thumbFilename = $imageBase.'-'.uniqid().'-thumb.png';

        $path = "{$directory}/{$filename}";
        $thumbPath = "{$directory}/thumbs/{$thumbFilename}";

        Storage::disk('public')->put($path, file_get_contents($fullSource));
        Storage::disk('public')->put($thumbPath, file_get_contents($thumbSource));

        return ['path' => $path, 'thumb_path' => $thumbPath];
    }
}
