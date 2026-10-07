<?php

namespace App\Http\Controllers;

use App\Facades\Cart;
use App\Http\Requests\AddToCartRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
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
