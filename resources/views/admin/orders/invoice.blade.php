<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }} — {{ setting('shop_name', config('shop.name')) }}</title>
    @include('partials.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Great+Vibes&family=Lato:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .invoice-sheet { box-shadow: none !important; margin: 0 !important; }
        }
    </style>
</head>
<body class="bg-beige p-6 font-sans text-espresso sm:p-10">

    <div class="no-print mx-auto mb-6 flex max-w-3xl justify-end gap-3">
        <button onclick="window.print()" class="btn-primary">Print / Save as PDF</button>
    </div>

    <div class="invoice-sheet mx-auto max-w-3xl rounded-xl2 bg-beige-surface p-8 shadow-soft sm:p-12">
        <div class="flex items-start justify-between border-b border-blush pb-6">
            <div>
                <x-logo size="lg" :link="false" />
                <div class="mt-4 text-sm text-espresso/70">
                    @if ($addr = setting('contact_address')) <p>{{ $addr }}</p> @endif
                    @if ($phone = setting('contact_phone')) <p>{{ $phone }}</p> @endif
                    @if ($email = setting('contact_email')) <p>{{ $email }}</p> @endif
                </div>
            </div>
            <div class="text-right">
                <h1 class="font-serif text-2xl text-espresso">Invoice</h1>
                <p class="mt-1 text-sm text-espresso/70">#{{ $order->order_number }}</p>
                <p class="text-sm text-espresso/70">{{ $order->created_at->format('d M Y') }}</p>
                <div class="mt-2"><x-order-status-badge :status="$order->status" /></div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-6">
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wide text-mocha">Billed / Delivered To</h2>
                <p class="mt-1 text-sm text-espresso">{{ $order->name }}</p>
                <p class="text-sm text-espresso/80">{{ $order->phone }}</p>
                @if ($order->email)<p class="text-sm text-espresso/80">{{ $order->email }}</p>@endif
                <p class="text-sm text-espresso/80">{{ $order->delivery_address }}</p>
            </div>
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wide text-mocha">Delivery</h2>
                <p class="mt-1 text-sm text-espresso">{{ $order->delivery_date->format('d M Y') }}</p>
                <p class="text-sm text-espresso/80">{{ $order->delivery_time_slot }}</p>
                <h2 class="mt-3 text-xs font-semibold uppercase tracking-wide text-mocha">Payment Method</h2>
                <p class="text-sm text-espresso/80">{{ $order->paymentMethodLabel() }}</p>
            </div>
        </div>

        <table class="mt-8 w-full text-left text-sm">
            <thead>
                <tr class="border-b border-blush text-xs uppercase tracking-wide text-mocha">
                    <th class="py-2">Item</th>
                    <th class="py-2 text-center">Qty</th>
                    <th class="py-2 text-right">Unit Price</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-blush">
                @foreach ($order->items as $item)
                    <tr>
                        <td class="py-3 text-espresso">{{ $item->product_title }}</td>
                        <td class="py-3 text-center text-espresso/80">{{ $item->quantity }}</td>
                        <td class="py-3 text-right"><x-price :amount="$item->unit_price" for="image" /></td>
                        <td class="py-3 text-right"><x-price :amount="$item->line_total" for="image" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4 flex justify-end">
            <div class="w-56 space-y-1 text-sm">
                <div class="flex justify-between text-espresso/80"><span>Subtotal</span><x-price :amount="$order->subtotal" for="image" /></div>
                <div class="flex justify-between text-espresso/80"><span>Delivery Fee</span><x-price :amount="$order->delivery_fee" for="image" /></div>
                <div class="flex justify-between border-t border-blush pt-2 text-base font-bold text-espresso"><span>Total</span><x-price :amount="$order->total" for="image" /></div>
            </div>
        </div>

        @if ($order->gift_message || $order->special_notes)
            <div class="mt-8 border-t border-blush pt-4 text-sm text-espresso/80">
                @if ($order->gift_message)<p><strong class="text-espresso">Gift Message:</strong> {{ $order->gift_message }}</p>@endif
                @if ($order->special_notes)<p class="mt-1"><strong class="text-espresso">Notes:</strong> {{ $order->special_notes }}</p>@endif
            </div>
        @endif

        <p class="mt-10 text-center text-xs text-espresso/50">Thank you for ordering from {{ setting('shop_name', config('shop.name')) }}.</p>
    </div>

</body>
</html>
