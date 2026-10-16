<?php

namespace App\Http\Controllers;

use App\Facades\Cart;
use App\Http\Requests\AddCustomBouquetRequest;
use App\Http\Requests\AddToCartRequest;
use App\Models\Flower;
use App\Models\Product;
use App\Models\Ribbon;
use App\Models\WrappingPaper;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(protected ImageService $images) {}

    public function index(): View
    {
        // The cart table itself is rendered by the <livewire:cart-page />
        // component for live quantity/remove updates; this view just
        // supplies the page chrome.
        return view('cart.index');
    }

    public function add(AddToCartRequest $request, Product $product): RedirectResponse
    {
        if (! $product->is_active) {
            return back()->with('error', 'That product is no longer available.');
        }

        if (! $product->in_stock) {
            return back()->with('error', "{$product->title} is currently out of stock.");
        }

        Cart::add($product, (int) $request->validated('quantity'), $request->validated('gift_message'));

        return redirect()
            ->route('cart.index')
            ->with('success', "{$product->title} was added to your cart.");
    }

    public function addCustom(AddCustomBouquetRequest $request): RedirectResponse
    {
        // The builder page only renders active, in-stock flowers, but the
        // request body is attacker-controlled — re-check against the DB
        // rather than trusting which flower_ids were posted. A flower with
        // zero stock is dropped the same way an inactive one is; this is
        // only a first line of defense though — OrderService::placeOrder()
        // does the real, race-safe stock check at checkout.
        $availableIds = Flower::query()->active()->inStock()->pluck('id');

        $selections = collect($request->validated('flowers'))
            ->filter(fn ($quantity, $flowerId) => (int) $quantity > 0 && $availableIds->contains((int) $flowerId))
            ->map(fn ($quantity, $flowerId) => ['flower_id' => (int) $flowerId, 'quantity' => (int) $quantity])
            ->values()
            ->all();

        if (empty($selections)) {
            return back()->withInput()->with('error', 'Please choose at least one available flower for your custom bouquet.');
        }

        // Same defense-in-depth re-check for the wrapping paper/ribbon
        // choice — only accept an id that's actually active and in stock
        // right now.
        $wrappingPaperId = $request->validated('wrapping_paper_id');
        if ($wrappingPaperId && ! WrappingPaper::query()->active()->inStock()->whereKey($wrappingPaperId)->exists()) {
            $wrappingPaperId = null;
        }

        $ribbonId = $request->validated('ribbon_id');
        if ($ribbonId && ! Ribbon::query()->active()->inStock()->whereKey($ribbonId)->exists()) {
            $ribbonId = null;
        }

        $type = $request->validated('type') === 'boutonniere' ? 'boutonniere' : 'bouquet';

        // Uploaded eagerly, here, rather than at checkout — the cart row is
        // just a session array and can't carry an actual file across
        // requests, so only the resulting storage path travels with it.
        $referenceImagePath = $request->hasFile('reference_image')
            ? $this->images->storeSingle($request->file('reference_image'), 'custom-bouquet-references')
            : null;

        Cart::addCustom(
            $selections,
            $type,
            $wrappingPaperId,
            $ribbonId,
            $referenceImagePath,
            (int) ($request->validated('quantity') ?? 1),
            $request->validated('gift_message'),
        );

        $label = $type === 'boutonniere' ? 'Your custom boutonnière' : 'Your custom bouquet';

        return redirect()->route('cart.index')->with('success', "{$label} was added to your cart.");
    }

    public function update(Request $request, string $rowId): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        Cart::update($rowId, (int) $request->integer('quantity'));

        return back()->with('success', 'Cart updated.');
    }

    public function remove(string $rowId): RedirectResponse
    {
        Cart::remove($rowId);

        return back()->with('success', 'Item removed from your cart.');
    }
}
