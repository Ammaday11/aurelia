<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function __construct(protected ImageService $images) {}

    public function destroy(Product $product, ProductImage $image): RedirectResponse
    {
        $this->authorize('update', $product);
        abort_unless($image->product_id === $product->id, 404);

        $this->images->delete($image->path);
        $this->images->delete($image->thumb_path);
        $image->delete();

        return back()->with('success', 'Image removed.');
    }

    /**
     * Persist drag-to-reorder changes from the product edit screen.
     * Expects { order: [imageId, imageId, ...] } in display order.
     */
    public function reorder(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:product_images,id'],
        ]);

        $imageIds = $product->images()->pluck('id');

        foreach ($request->input('order') as $position => $imageId) {
            if ($imageIds->contains($imageId)) {
                ProductImage::whereKey($imageId)->update(['sort_order' => $position]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
