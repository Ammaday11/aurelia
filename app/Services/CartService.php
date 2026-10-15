<?php

namespace App\Services;

use App\Models\Flower;
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
 *     // ...or, for a custom bouquet built on /custom-bouquet:
 *     '<rowId>' => ['custom_flowers' => [['flower_id' => 3, 'quantity' => 6], ...], 'quantity' => 1, 'gift_message' => '...'],
 * ]
 *
 * A row is a catalogue-product line if it has a 'product_id' key, or a
 * custom-bouquet line if it has a 'custom_flowers' key — content() resolves
 * either shape into the same object shape (is_custom, unit_price,
 * line_total, ...) so callers (cart view, checkout, OrderService) don't
 * need to know the session layout.
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
            if (($item['product_id'] ?? null) === $product->id && ($item['gift_message'] ?? null) === $giftMessage) {
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

    /**
     * Add a custom bouquet — a flower-type => stem-count selection built on
     * the /custom-bouquet page — to the cart.
     *
     * @param  array<int, array{flower_id: int, quantity: int}>  $flowerSelections  Already filtered to quantity > 0 by the caller (see AddCustomBouquetRequest).
     * @param  int  $quantity  How many of this exact bouquet to order.
     */
    public function addCustom(array $flowerSelections, int $quantity = 1, ?string $giftMessage = null): string
    {
        $quantity = max(1, $quantity);
        $giftMessage = blank($giftMessage) ? null : trim($giftMessage);
        $selection = $this->normalizeSelection($flowerSelections);
        $items = $this->raw();

        foreach ($items as $rowId => $item) {
            if (
                isset($item['custom_flowers'])
                && $this->normalizeSelection($item['custom_flowers']) === $selection
                && ($item['gift_message'] ?? null) === $giftMessage
            ) {
                $items[$rowId]['quantity'] += $quantity;
                $this->save($items);

                return $rowId;
            }
        }

        $rowId = (string) Str::uuid();
        $items[$rowId] = [
            'custom_flowers' => $flowerSelections,
            'quantity' => $quantity,
            'gift_message' => $giftMessage,
        ];
        $this->save($items);

        return $rowId;
    }

    /**
     * A stable, order-independent signature for a flower selection, so
     * "6 roses + 2 lilies" added twice merges into one row regardless of
     * the order the flowers were picked in.
     */
    protected function normalizeSelection(array $flowerSelections): string
    {
        $pairs = collect($flowerSelections)
            ->map(fn (array $s) => [(int) $s['flower_id'], (int) $s['quantity']])
            ->sortBy(fn (array $pair) => $pair[0])
            ->values()
            ->all();

        return json_encode($pairs);
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
     * Raw session line items, unlike content() which hydrates Product/
     * Flower models. Used by OrderService, which re-fetches them itself
     * (products under a row lock) during checkout.
     */
    public function items(): array
    {
        return $this->raw();
    }

    /**
     * Resolved cart lines — product/flower models eager-loaded, quantity
     * clamped to available stock (products only; flowers have none), and
     * a line_total computed from *current* prices — the cart always
     * reflects live prices/stock; the order's own totals are what get
     * frozen at checkout time.
     */
    public function content(): Collection
    {
        $items = $this->raw();

        if (empty($items)) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('id', collect($items)->filter(fn (array $i) => isset($i['product_id']))->pluck('product_id')->unique())
            ->get()
            ->keyBy('id');

        $flowerIds = collect($items)
            ->filter(fn (array $i) => isset($i['custom_flowers']))
            ->flatMap(fn (array $i) => collect($i['custom_flowers'])->pluck('flower_id'))
            ->unique();

        $flowers = Flower::query()->whereIn('id', $flowerIds)->get()->keyBy('id');

        return collect($items)
            ->map(function (array $item, string $rowId) use ($products, $flowers) {
                return isset($item['custom_flowers'])
                    ? $this->resolveCustomRow($rowId, $item, $flowers)
                    : $this->resolveProductRow($rowId, $item, $products);
            })
            ->filter()
            ->values();
    }

    protected function resolveProductRow(string $rowId, array $item, Collection $products): ?object
    {
        /** @var Product|null $product */
        $product = $products->get($item['product_id']);

        if (! $product) {
            return null;
        }

        $quantity = min($item['quantity'], max($product->stock_quantity, 0) ?: $item['quantity']);
        $unitPrice = $product->current_price;

        return (object) [
            'row_id' => $rowId,
            'is_custom' => false,
            'product' => $product,
            'flowers' => null,
            'title' => $product->title,
            'quantity' => $quantity,
            'requested_quantity' => $item['quantity'],
            'gift_message' => $item['gift_message'] ?? null,
            'unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $quantity, 2),
            'exceeds_stock' => $item['quantity'] > $product->stock_quantity,
        ];
    }

    protected function resolveCustomRow(string $rowId, array $item, Collection $flowers): ?object
    {
        $lines = collect($item['custom_flowers'])
            ->map(function (array $selection) use ($flowers) {
                /** @var Flower|null $flower */
                $flower = $flowers->get($selection['flower_id']);

                if (! $flower) {
                    return null;
                }

                $quantity = max(1, (int) $selection['quantity']);

                return (object) [
                    'flower' => $flower,
                    'quantity' => $quantity,
                    'unit_price' => (float) $flower->price_per_stem,
                    'line_total' => round((float) $flower->price_per_stem * $quantity, 2),
                ];
            })
            ->filter()
            ->values();

        // Every flower this row referenced is gone — drop the whole row,
        // same as a product row whose product no longer exists.
        if ($lines->isEmpty()) {
            return null;
        }

        $quantity = max(1, (int) ($item['quantity'] ?? 1));
        $unitPrice = round((float) $lines->sum('line_total'), 2);

        return (object) [
            'row_id' => $rowId,
            'is_custom' => true,
            'product' => null,
            'flowers' => $lines,
            'title' => $this->customTitle($lines),
            'quantity' => $quantity,
            'requested_quantity' => $quantity,
            'gift_message' => $item['gift_message'] ?? null,
            'unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $quantity, 2),
            'exceeds_stock' => false,
        ];
    }

    public function customTitle(Collection $lines): string
    {
        return 'Custom Bouquet ('.$lines->map(fn ($l) => "{$l->quantity}\u{00D7} {$l->flower->name}")->implode(', ').')';
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
