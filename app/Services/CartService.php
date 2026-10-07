<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * A session-backed shopping cart — no database table, no account needed,
 * matching the guest-checkout requirement. The session simply stores an
 * array of line items keyed by a random row id so the same product can
 * appear more than once with a different gift message.
 *
 * Session shape: 'cart' => [
 *     '<rowId>' => ['product_id' => 1, 'quantity' => 2, 'gift_message' => '...'],
 *     ...
 * ]
 */
class CartService
{
    protected const SESSION_KEY = 'cart';

    public function __construct(protected SessionManager $session) {}

    /**
     * Add a product to the cart. If the same product is already in the
     * cart with the exact same gift message, quantities are merged;
     * otherwise a new line item is created (so "roses, no note" and
     * "roses, for mum" stay separate lines).
     */
    public function add(Product $product, int $quantity = 1, ?string $giftMessage = null): string
    {
        $quantity = max(1, $quantity);
        $giftMessage = blank($giftMessage) ? null : trim($giftMessage);
        $items = $this->raw();

        foreach ($items as $rowId => $item) {
            if ($item['product_id'] === $product->id && ($item['gift_message'] ?? null) === $giftMessage) {
                $items[$rowId]['quantity'] += $quantity;
                $this->save($items);

                return $rowId;
            }
        }

        $rowId = (string) Str::uuid();
        $items[$rowId] = [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'gift_message' => $giftMessage,
        ];
        $this->save($items);

        return $rowId;
    }

    public function update(string $rowId, int $quantity): void
    {
        $items = $this->raw();

        if (! isset($items[$rowId])) {
            return;
        }

        if ($quantity < 1) {
            unset($items[$rowId]);
        } else {
            $items[$rowId]['quantity'] = $quantity;
        }

        $this->save($items);
    }

    public function remove(string $rowId): void
    {
        $items = $this->raw();
        unset($items[$rowId]);
        $this->save($items);
    }

    public function clear(): void
    {
        $this->save([]);
    }

    public function isEmpty(): bool
    {
        return empty($this->raw());
    }

    /**
     * Raw session line items (product_id, quantity, gift_message), unlike
     * content() which hydrates Product models. Used by OrderService, which
     * re-fetches products itself under a row lock during checkout.
     */
    public function items(): array
    {
        return $this->raw();
    }

    /**
     * Resolved cart lines: product eager-loaded, quantity clamped to
     * available stock, and a line_total computed from the product's
     * *current* price — the cart always reflects live prices/stock; the
     * order's own totals are what get frozen at checkout time.
     */
    public function content(): Collection
    {
        $items = $this->raw();

        if (empty($items)) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('id', collect($items)->pluck('product_id')->unique())
            ->get()
            ->keyBy('id');

        return collect($items)
            ->map(function (array $item, string $rowId) use ($products) {
                /** @var Product|null $product */
                $product = $products->get($item['product_id']);

                if (! $product) {
                    return null;
                }

                $quantity = min($item['quantity'], max($product->stock_quantity, 0) ?: $item['quantity']);
                $unitPrice = $product->current_price;

                return (object) [
                    'row_id' => $rowId,
                    'product' => $product,
                    'quantity' => $quantity,
                    'requested_quantity' => $item['quantity'],
                    'gift_message' => $item['gift_message'] ?? null,
                    'unit_price' => $unitPrice,
                    'line_total' => round($unitPrice * $quantity, 2),
                    'exceeds_stock' => $item['quantity'] > $product->stock_quantity,
                ];
            })
            ->filter()
            ->values();
    }

    public function count(): int
    {
        return (int) $this->content()->sum('quantity');
    }

    public function subtotal(): float
    {
        return round((float) $this->content()->sum('line_total'), 2);
    }

    protected function raw(): array
    {
        return $this->session->get(self::SESSION_KEY, []);
    }

    protected function save(array $items): void
    {
        $this->session->put(self::SESSION_KEY, $items);
    }
}
