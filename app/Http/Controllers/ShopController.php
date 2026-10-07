<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    /**
     * Product grid with category filter, price sort, search, and pagination.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,name_asc'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Product::query()
            ->with(['images', 'category'])
            ->active()
            ->search($validated['q'] ?? null);

        if (! empty($validated['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $validated['category']));
        }

        $query = match ($validated['sort'] ?? 'newest') {
            'price_asc' => $query->orderByRaw('COALESCE(sale_price, price) asc'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, price) desc'),
            'name_asc' => $query->orderBy('title'),
            default => $query->latest(),
        };

        $products = $query->paginate(config('shop.products_per_page'))->withQueryString();

        return view('shop.index', [
            'products' => $products,
            'categories' => Category::active()->ordered()->withCount('activeProducts')->get(),
            'activeCategory' => $validated['category'] ?? null,
            'sort' => $validated['sort'] ?? 'newest',
            'search' => $validated['q'] ?? null,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'category']);

        $related = Product::query()
            ->with('images')
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('shop.show', [
            'product' => $product,
            'related' => $related,
        ]);
    }
}
