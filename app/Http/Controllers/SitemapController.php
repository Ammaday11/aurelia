<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', 3600, function () {
            $urls = collect([
                ['loc' => route('home'), 'priority' => '1.0'],
                ['loc' => route('shop.index'), 'priority' => '0.9'],
                ['loc' => route('pages.about'), 'priority' => '0.5'],
                ['loc' => route('pages.contact'), 'priority' => '0.5'],
                ['loc' => route('orders.track.show'), 'priority' => '0.3'],
            ]);

            Product::query()->active()->get(['slug', 'updated_at'])->each(function (Product $product) use ($urls) {
                $urls->push([
                    'loc' => route('shop.show', $product),
                    'lastmod' => $product->updated_at->toAtomString(),
                    'priority' => '0.8',
                ]);
            });

            Category::query()->active()->get(['slug', 'updated_at'])->each(function (Category $category) use ($urls) {
                $urls->push([
                    'loc' => route('shop.index', ['category' => $category->slug]),
                    'lastmod' => $category->updated_at->toAtomString(),
                    'priority' => '0.6',
                ]);
            });

            return view('partials.sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
