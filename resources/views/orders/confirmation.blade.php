<x-storefront-layout title="Order Confirmed" description="Your Aurelia Boutonnière order has been placed.">

    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="text-center">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sand/20 text-mocha">
                <x-icon name="check" class="h-8 w-8" />
            </span>
            <h1 class="mt-6 font-serif text-3xl text-espresso sm:text-4xl">Thank you, {{ $order->name }}!</h1>
            <p class="mt-2 text-espresso/80">Your order has been received and is being prepared.</p>
            <p class="mt-4 inline-block rounded-full bg-blush px-5 py-2 font-semibold text-espresso">
                Order #{{ $order->order_number }}
            </p>
        </div>

        <div class="card mt-10 p-6 sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-blush pb-5">
                <div>
                    <p class="text-xs uppercase tracking-wide text-mocha">Status</p>
                    <p class="font-semibold text-espresso">{{ $order->statusLabel() }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-mocha">Delivery</p>
                    <p class="font-semibold text-espresso">{{ $order->delivery_date->format('d M Y') }} &middot; {{ $order->delivery_time_slot }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-mocha">Payment</p>
                    <p class="font-semibold text-espresso">{{ $order->paymentMethodLabel() }}</p>
                </div>
            </div>

            <ul class="mt-5 space-y-3">
                @foreach ($order->items as $item)
                    <li class="flex items-center justify-between text-sm">
                        <div>
                            <p class="font-medium text-espresso">{{ $item->isCustom() ? 'Custom Bouquet' : $item->product_title }} &times; {{ $item->quantity }}</p>
                            <x-custom-bouquet-summary :item="$item" />
                            @if ($item->gift_message)
                                <p class="text-xs italic text-espresso/60">Gift note: "{{ $item->gift_message }}"</p>
                            @endif
                        </div>
                        <x-price :amount="$item->line_total" class="text-espresso" />
                    </li>
                @endforeach
            </ul>

            <div class="mt-5 space-y-2 border-t border-blush pt-5 text-sm">
                <x-order-totals :order="$order" />
                <div class="flex justify-between border-t border-blush pt-2 text-base font-bold text-espresso"><span>Total</span><x-price :amount="$order->total" /></div>
            </div>

            <div class="mt-6 border-t border-blush pt-5 text-sm text-espresso/80">
                <p><span class="font-semibold text-espresso">Delivering to:</span> {{ $order->delivery_address }}</p>
                @if ($order->special_notes)
                    <p class="mt-1"><span class="font-semibold text-espresso">Notes:</span> {{ $order->special_notes }}</p>
                @endif
            </div>
        </div>

        <div class="mt-8 flex flex-col items-center gap-3 text-center">
            @if ($order->email)
                <p class="text-sm text-espresso/70">A confirmation email was sent to {{ $order->email }}.</p>
            @endif
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('orders.track.show') }}" class="btn-outline">Track This Order</a>
                <a href="{{ route('shop.index') }}" class="btn-primary">Continue Shopping</a>
            </div>
        </div>
    </section>

</x-storefront-layout>
