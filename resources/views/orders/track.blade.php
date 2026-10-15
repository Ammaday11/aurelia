<x-storefront-layout title="Track Your Order" description="Check the status of your Aurelia Boutonnière order.">

    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="font-script text-3xl text-mocha">Where's my order?</p>
            <h1 class="mt-2 font-serif text-4xl text-espresso">Track Your Order</h1>
            <p class="mt-2 text-espresso/80">Enter your order number and phone number to see its current status.</p>
        </div>

        <form method="POST" action="{{ route('orders.track.result') }}" class="card mt-10 grid grid-cols-1 gap-5 p-6 sm:grid-cols-3 sm:p-8">
            @csrf
            <div class="sm:col-span-1">
                <x-input-label for="order_number" value="Order Number" />
                <x-text-input id="order_number" name="order_number" class="mt-1 w-full" value="{{ old('order_number') }}" placeholder="AUR-20261007-XXXX" required />
            </div>
            <div class="sm:col-span-1">
                <x-input-label for="phone" value="Phone Number" />
                <x-text-input id="phone" name="phone" class="mt-1 w-full" value="{{ old('phone') }}" required />
            </div>
            <div class="flex items-end sm:col-span-1">
                <button type="submit" class="btn-primary w-full">Track Order</button>
            </div>
            <x-input-error :messages="$errors->get('order_number')" class="sm:col-span-3" />
        </form>

        @if (! empty($searched))
            @if ($order)
                <div class="card mt-10 p-6 sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-mocha">Order</p>
                            <p class="font-semibold text-espresso">#{{ $order->order_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-mocha">Delivery</p>
                            <p class="font-semibold text-espresso">{{ $order->delivery_date->format('d M Y') }} &middot; {{ $order->delivery_time_slot }}</p>
                        </div>
                    </div>

                    {{-- Status timeline --}}
                    @if ($order->isCancelled())
                        <div class="mt-8 rounded-lg bg-red-100 p-4 text-center font-semibold text-red-800">
                            This order was cancelled.
                        </div>
                    @else
                        @php
                            $steps = \App\Models\Order::STATUSES;
                            $currentIndex = array_search($order->status, $steps, true);
                        @endphp
                        <div class="mt-10 flex items-center justify-between">
                            @foreach ($steps as $i => $step)
                                <div class="flex flex-1 flex-col items-center text-center">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold
                                                {{ $i <= $currentIndex ? 'bg-mocha text-beige-surface' : 'bg-blush text-mocha' }}">
                                        {{ $i + 1 }}
                                    </div>
                                    <p class="mt-2 text-[11px] font-semibold uppercase tracking-wide {{ $i <= $currentIndex ? 'text-espresso' : 'text-espresso/40' }}">
                                        {{ order_status_label($step) }}
                                    </p>
                                </div>
                                @if (! $loop->last)
                                    <div class="h-0.5 flex-1 {{ $i < $currentIndex ? 'bg-mocha' : 'bg-blush' }}"></div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <ul class="mt-10 space-y-3 border-t border-blush pt-5">
                        @foreach ($order->items as $item)
                            <li class="flex items-start justify-between text-sm">
                                <div>
                                    <span class="text-espresso">{{ $item->isCustom() ? $item->customTypeLabel() : $item->product_title }} &times; {{ $item->quantity }}</span>
                                    <x-custom-bouquet-summary :item="$item" />
                                </div>
                                <x-price :amount="$item->line_total" class="flex-shrink-0 text-espresso" />
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 space-y-2 border-t border-blush pt-4 text-sm">
                        <x-order-totals :order="$order" />
                        <div class="flex justify-between border-t border-blush pt-2 text-base font-bold text-espresso">
                            <span>Total</span>
                            <x-price :amount="$order->total" />
                        </div>
                    </div>
                </div>
            @else
                <div class="card mt-10 p-8 text-center">
                    <p class="font-semibold text-espresso">We couldn't find a matching order.</p>
                    <p class="mt-2 text-sm text-espresso/70">Double-check your order number and phone number, or contact us if you need help.</p>
                </div>
            @endif
        @endif
    </section>

</x-storefront-layout>
