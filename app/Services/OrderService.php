<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\ItemUnavailableException;
use App\Models\CustomCharge;
use App\Models\Flower;
use App\Models\Order;
use App\Models\OrderCharge;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Ribbon;
use App\Models\Setting;
use App\Models\WrappingPaper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Turns the current session cart into a persisted Order.
 *
 * Everything that matters for money and inventory happens inside one DB
 * transaction with the relevant product rows locked (SELECT ... FOR
 * UPDATE), so two guests checking out the last unit of a bouquet at the
 * same instant can't both succeed — the second one gets a clear
 * InsufficientStockException instead of silently overselling. Custom
 * bouquet lines (see CartService::addCustom()) have no stock to guard —
 * flowers/wrapping paper/ribbon are only checked for still being active —
 * so they don't need row locking.
 *
 * Totals are always recalculated here from the server's own product/
 * flower/wrapping/ribbon prices — the client/cart's numbers are never
 * trusted for the charge.
 */
class OrderService
{
    public function __construct(protected CartService $cart) {}

    /**
     * @param  array  $details  Validated checkout form data (name, phone,
     *                          email, delivery_address, delivery_date,
     *                          delivery_time_slot, gift_message,
     *                          special_notes, payment_method).
     *
     * @throws InsufficientStockException
     * @throws ItemUnavailableException
     */
    public function placeOrder(array $details, ?UploadedFile $receipt, ?string $ip): Order
    {
        $cartItems = $this->cart->items();

        if (empty($cartItems)) {
            throw new \RuntimeException('Your cart is empty.');
        }

        return DB::transaction(function () use ($details, $receipt, $ip, $cartItems) {
            $productRows = collect($cartItems)->filter(fn (array $i) => isset($i['product_id']));
            $customRows = collect($cartItems)->filter(fn (array $i) => isset($i['custom_flowers']));

            // Lock the rows we're about to decrement so a concurrent
            // checkout can't read a stale stock_quantity.
            $products = Product::query()
                ->whereIn('id', $productRows->pluck('product_id')->unique())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $flowerIds = $customRows->flatMap(fn (array $i) => collect($i['custom_flowers'])->pluck('flower_id'))->unique();
            $flowers = Flower::query()->whereIn('id', $flowerIds)->get()->keyBy('id');

            $wrappingPaperIds = $customRows->pluck('wrapping_paper_id')->filter()->unique();
            $wrappingPapers = WrappingPaper::query()->whereIn('id', $wrappingPaperIds)->get()->keyBy('id');

            $ribbonIds = $customRows->pluck('ribbon_id')->filter()->unique();
            $ribbons = Ribbon::query()->whereIn('id', $ribbonIds)->get()->keyBy('id');

            $subtotal = 0.0;
            $lines = [];

            foreach ($productRows as $item) {
                /** @var Product|null $product */
                $product = $products->get($item['product_id']);

                if (! $product || ! $product->is_active) {
                    throw new InsufficientStockException($item['product_id'] ? "Product #{$item['product_id']}" : 'Item', 0);
                }

                if ($product->stock_quantity < $item['quantity']) {
                    throw new InsufficientStockException($product->title, $product->stock_quantity);
                }

                $unitPrice = $product->current_price;
                $lineTotal = round($unitPrice * $item['quantity'], 2);
                $subtotal += $lineTotal;

                $lines[] = [
                    'product' => $product,
                    'product_title' => $product->title,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'gift_message' => $item['gift_message'],
                    'custom_flowers' => null,
                    'wrapping_paper_name' => null,
                    'wrapping_paper_price' => null,
                    'ribbon_name' => null,
                    'ribbon_price' => null,
                    'custom_type' => null,
                    'reference_image_path' => null,
                ];
            }

            foreach ($customRows as $item) {
                $flowerLines = [];

                foreach ($item['custom_flowers'] as $selection) {
                    /** @var Flower|null $flower */
                    $flower = $flowers->get($selection['flower_id']);

                    if (! $flower || ! $flower->is_active) {
                        throw new ItemUnavailableException($flower?->name ?? "Flower #{$selection['flower_id']}");
                    }

                    $qty = max(1, (int) $selection['quantity']);

                    $flowerLines[] = [
                        'flower_id' => $flower->id,
                        'name' => $flower->name,
                        'unit_price' => (float) $flower->price_per_stem,
                        'quantity' => $qty,
                    ];
                }

                $wrappingPaper = null;
                if (! empty($item['wrapping_paper_id'])) {
                    $wrappingPaper = $wrappingPapers->get($item['wrapping_paper_id']);

                    if (! $wrappingPaper || ! $wrappingPaper->is_active) {
                        throw new ItemUnavailableException($wrappingPaper?->name ?? "Wrapping paper #{$item['wrapping_paper_id']}");
                    }
                }

                $ribbon = null;
                if (! empty($item['ribbon_id'])) {
                    $ribbon = $ribbons->get($item['ribbon_id']);

                    if (! $ribbon || ! $ribbon->is_active) {
                        throw new ItemUnavailableException($ribbon?->name ?? "Ribbon #{$item['ribbon_id']}");
                    }
                }

                $bouquetUnitPrice = round(
                    collect($flowerLines)->sum(fn (array $l) => $l['unit_price'] * $l['quantity'])
                        + ($wrappingPaper ? (float) $wrappingPaper->price : 0)
                        + ($ribbon ? (float) $ribbon->price : 0),
                    2
                );
                $bouquetQty = max(1, (int) ($item['quantity'] ?? 1));
                $lineTotal = round($bouquetUnitPrice * $bouquetQty, 2);
                $subtotal += $lineTotal;

                $type = ($item['type'] ?? 'bouquet') === 'boutonniere' ? 'boutonniere' : 'bouquet';
                $label = $type === 'boutonniere' ? 'Custom Boutonnière' : 'Custom Bouquet';
                $title = "{$label} (".collect($flowerLines)->map(fn (array $l) => "{$l['quantity']}\u{00D7} {$l['name']}")->implode(', ').')';

                $lines[] = [
                    'product' => null,
                    'product_title' => $title,
                    'quantity' => $bouquetQty,
                    'unit_price' => $bouquetUnitPrice,
                    'line_total' => $lineTotal,
                    'gift_message' => $item['gift_message'] ?? null,
                    'custom_flowers' => $flowerLines,
                    'wrapping_paper_name' => $wrappingPaper?->name,
                    'wrapping_paper_price' => $wrappingPaper ? (float) $wrappingPaper->price : null,
                    'ribbon_name' => $ribbon?->name,
                    'ribbon_price' => $ribbon ? (float) $ribbon->price : null,
                    'custom_type' => $type,
                    'reference_image_path' => $item['reference_image_path'] ?? null,
                ];
            }

            $subtotal = round($subtotal, 2);
            $deliveryFee = (float) Setting::get('delivery_fee', config('shop.default_delivery_fee'));

            // Every active "automatic" charge is added to this order's
            // total the same way delivery_fee is — no customer choice
            // involved (see CustomCharge::is_automatic). A non-automatic
            // charge is never applied here; an admin attaches it to a
            // specific order afterwards (see attachCharge()).
            $automaticCharges = CustomCharge::query()->active()->automatic()->ordered()->get();
            $chargesTotal = round((float) $automaticCharges->sum('amount'), 2);

            $total = round($subtotal + $deliveryFee + $chargesTotal, 2);

            $receiptPath = null;
            if ($receipt) {
                $receiptPath = $receipt->store('receipts', 'public');
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'name' => $details['name'],
                'phone' => $details['phone'],
                'email' => $details['email'] ?? null,
                'delivery_address' => $details['delivery_address'],
                'delivery_date' => $details['delivery_date'],
                'delivery_time_slot' => $details['delivery_time_slot'],
                'gift_message' => $details['gift_message'] ?? null,
                'special_notes' => $details['special_notes'] ?? null,
                'payment_method' => $details['payment_method'],
                'payment_receipt_path' => $receiptPath,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'currency' => config('shop.currency.code'),
                'placed_from_ip' => $ip,
            ]);

            foreach ($lines as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']?->id,
                    'product_title' => $line['product_title'],
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                    'gift_message' => $line['gift_message'],
                    'custom_flowers' => $line['custom_flowers'],
                    'wrapping_paper_name' => $line['wrapping_paper_name'],
                    'wrapping_paper_price' => $line['wrapping_paper_price'],
                    'ribbon_name' => $line['ribbon_name'],
                    'ribbon_price' => $line['ribbon_price'],
                    'custom_type' => $line['custom_type'],
                    'reference_image_path' => $line['reference_image_path'],
                ]);

                // Block overselling: decrement atomically within the same
                // locked transaction. Custom (flower) lines have no stock
                // to decrement.
                $line['product']?->decrement('stock_quantity', $line['quantity']);
            }

            foreach ($automaticCharges as $charge) {
                OrderCharge::create([
                    'order_id' => $order->id,
                    'custom_charge_id' => $charge->id,
                    'name' => $charge->name,
                    'amount' => $charge->amount,
                    'is_automatic' => true,
                ]);
            }

            $this->cart->clear();

            return $order->load('items', 'charges');
        });
    }

    /**
     * Admin attaches a one-off charge (a non-automatic CustomCharge) to a
     * specific order — e.g. a "Rush Order Fee" that doesn't apply to every
     * order. name/amount are snapshotted the same way automatic charges
     * are at checkout. Recalculates and persists the order's total.
     */
    public function attachCharge(Order $order, CustomCharge $charge): Order
    {
        return DB::transaction(function () use ($order, $charge) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            OrderCharge::create([
                'order_id' => $order->id,
                'custom_charge_id' => $charge->id,
                'name' => $charge->name,
                'amount' => $charge->amount,
                'is_automatic' => false,
            ]);

            $order->total = $order->recalculateTotal();
            $order->save();

            return $order->load('items', 'charges');
        });
    }

    /**
     * Removes a previously attached charge from an order and recalculates
     * the total.
     */
    public function detachCharge(Order $order, OrderCharge $orderCharge): Order
    {
        return DB::transaction(function () use ($order, $orderCharge) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            abort_unless($orderCharge->order_id === $order->id, 404);
            $orderCharge->delete();

            $order->total = $order->recalculateTotal();
            $order->save();

            return $order->load('items', 'charges');
        });
    }

    /**
     * Moves an order to a new status in the admin workflow. Cancelling an
     * order restocks its items (the customer isn't getting them after
     * all); reinstating a previously-cancelled order decrements stock
     * again where possible, capped at zero rather than going negative if
     * that stock has since been sold elsewhere. Custom-bouquet items have
     * no product/stock, so they're a no-op here.
     */
    public function updateStatus(Order $order, string $newStatus): Order
    {
        return DB::transaction(function () use ($order, $newStatus) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->first();
            $wasCancelled = $order->status === Order::CANCELLED;
            $willBeCancelled = $newStatus === Order::CANCELLED;

            if (! $wasCancelled && $willBeCancelled) {
                foreach ($order->items as $item) {
                    $item->product?->increment('stock_quantity', $item->quantity);
                }
            } elseif ($wasCancelled && ! $willBeCancelled) {
                foreach ($order->items as $item) {
                    $item->product?->decrement('stock_quantity', min($item->quantity, $item->product->stock_quantity));
                }
            }

            $order->status = $newStatus;
            $order->cancelled_at = $willBeCancelled ? now() : null;
            $order->save();

            return $order;
        });
    }
}
