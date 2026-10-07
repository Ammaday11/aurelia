<?php

namespace App\Livewire;

use App\Facades\Cart;
use Livewire\Component;

/**
 * A fully reactive cart table: quantity changes and removals update the
 * subtotal instantly without a page reload (the cart itself still lives in
 * the session via CartService — Livewire just gives it a live UI).
 */
class CartPage extends Component
{
    /**
     * @var array<string, int> rowId => quantity, bound to each row's input
     */
    public array $quantities = [];

    public function mount(): void
    {
        $this->syncQuantities();
    }

    public function updateQuantity(string $rowId): void
    {
        $quantity = max(0, (int) ($this->quantities[$rowId] ?? 1));
        Cart::update($rowId, $quantity);
        $this->syncQuantities();
        $this->dispatch('cart-updated');
    }

    public function removeItem(string $rowId): void
    {
        Cart::remove($rowId);
        $this->syncQuantities();
        $this->dispatch('cart-updated');
    }

    protected function syncQuantities(): void
    {
        $this->quantities = Cart::content()
            ->mapWithKeys(fn ($item) => [$item->row_id => $item->quantity])
            ->all();
    }

    public function render()
    {
        return view('livewire.cart-page', [
            'items' => Cart::content(),
            'subtotal' => Cart::subtotal(),
        ]);
    }
}
