<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(protected ImageService $images) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::query()
            ->with(['category', 'images'])
            ->when($request->filled('q'), fn ($q) => $q->search($request->string('q')))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'title' => 'Products',
            'products' => $products,
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.create', [
            'title' => 'New Product',
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['images']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($data);

        $this->attachImages($product, $request->file('images', []));

        return redirect()->route('admin.products.index')->with('success', "\"{$product->title}\" was created.");
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $product->load('images');

        return view('admin.products.edit', [
            'title' => 'Edit Product',
            'product' => $product,
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except(['images']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');

        $product->update($data);

        $this->attachImages($product, $request->file('images', []));

        return redirect()->route('admin.products.index')->with('success', "\"{$product->title}\" was updated.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        if ($product->orderItems()->exists()) {
            return back()->with('error', "\"{$product->title}\" has existing orders and can't be deleted — hide it instead.");
        }

        foreach ($product->images as $image) {
            $this->images->delete($image->path);
            $this->images->delete($image->thumb_path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', "\"{$product->title}\" was deleted.");
    }

    /**
     * @param  UploadedFile[]  $files
     */
    protected function attachImages(Product $product, array $files): void
    {
        if (empty($files)) {
            return;
        }

        $nextSort = (int) $product->images()->max('sort_order') + 1;

        foreach ($files as $file) {
            $stored = $this->images->storeWithThumbnail($file, 'products/'.$product->id);

            $product->images()->create([
                'path' => $stored['path'],
                'thumb_path' => $stored['thumb_path'],
                'sort_order' => $nextSort++,
            ]);
        }
    }
}
