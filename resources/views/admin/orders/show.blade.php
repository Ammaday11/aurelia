<x-app-layout :title="'Order '.$order->order_number">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-mocha hover:underline">&larr; All Orders</a>
            <h2 class="mt-1 font-serif text-2xl text-espresso">Order #{{ $order->order_number }}</h2>
            <p class="text-sm text-espresso/60">Placed {{ $order->created_at->format('d M Y, g:ia') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <x-order-status-badge :status="$order->status" />
            <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="btn-outline">Print Invoice</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">

            <div class="card p-6">
                <h3 class="font-serif text-lg text-espresso">Items</h3>
                <table class="mt-4 w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-blush text-xs uppercase tracking-wide text-mocha">
                            <th class="py-2">Item</th>
                            <th class="py-2">Qty</th>
                            <th class="py-2 text-right">Unit Price</th>
                            <th class="py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blush">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="py-3">
                                    <p class="text-espresso">{{ $item->isCustom() ? $item->customTypeLabel() : $item->product_title }}</p>
                                    <x-custom-bouquet-summary :item="$item" />
                                    @if ($item->gift_message)
                                        <p class="text-xs italic text-espresso/60">Gift note: "{{ $item->gift_message }}"</p>
                                    @endif
                                    @if (! $item->isCustom() && ! $item->product)
                                        <p class="text-xs text-red-600">(product no longer in catalogue)</p>
                                    @endif
                                </td>
                                <td class="py-3 text-espresso/80">{{ $item->quantity }}</td>
                                <td class="py-3 text-right"><x-price :amount="$item->unit_price" /></td>
                                <td class="py-3 text-right"><x-price :amount="$item->line_total" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4 space-y-1 border-t border-blush pt-4 text-sm">
                    <x-order-totals :order="$order" />
                    <div class="flex justify-between border-t border-blush pt-2 text-base font-bold text-espresso"><span>Total</span><x-price :amount="$order->total" /></div>
                </div>
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h3 class="font-serif text-lg text-espresso">Charges</h3>
                </div>

                @if ($order->charges->isEmpty())
                    <p class="mt-2 text-sm text-espresso/60">No extra charges on this order.</p>
                @else
                    <ul class="mt-3 divide-y divide-blush">
                        @foreach ($order->charges as $charge)
                            <li class="flex items-center justify-between gap-3 py-2 text-sm">
                                <div>
                                    <span class="text-espresso">{{ $charge->name }}</span>
                                    @if ($charge->is_automatic)
                                        <span class="badge ml-2 bg-sand/30 text-espresso">automatic</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-price :amount="$charge->amount" />
                                    <form method="POST" action="{{ route('admin.orders.charges.detach', [$order, $charge]) }}"
                                          onsubmit="return confirm('Remove this charge from the order?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Remove</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($availableCharges->isNotEmpty())
                    <form method="POST" action="{{ route('admin.orders.charges.attach', $order) }}" class="mt-4 flex items-end gap-3">
                        @csrf
                        <div class="flex-1">
                            <x-input-label for="custom_charge_id" value="Add a charge" />
                            <select id="custom_charge_id" name="custom_charge_id" class="input mt-1 w-full">
                                @foreach ($availableCharges as $available)
                                    {{-- Plain "MVR" here, not money()'s Rufiyaa-sign glyph: native
                                         <option> text renders with the OS/system font, which can't
                                         resolve the scoped custom webfont, so the sign would show
                                         as a tofu box inside the dropdown. --}}
                                    <option value="{{ $available->id }}">{{ $available->name }} (MVR {{ money($available->amount, false) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-outline">Add</button>
                    </form>
                @endif
            </div>

            <div class="card p-6">
                <h3 class="font-serif text-lg text-espresso">Delivery &amp; Notes</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div><dt class="font-semibold text-espresso">Address</dt><dd class="text-espresso/80">{{ $order->delivery_address }}</dd></div>
                    <div><dt class="font-semibold text-espresso">Date &amp; Time</dt><dd class="text-espresso/80">{{ $order->delivery_date->format('d M Y') }} &middot; {{ $order->delivery_time_slot }}</dd></div>
                    @if ($order->gift_message)
                        <div><dt class="font-semibold text-espresso">Gift Message</dt><dd class="text-espresso/80">{{ $order->gift_message }}</dd></div>
                    @endif
                    @if ($order->special_notes)
                        <div><dt class="font-semibold text-espresso">Special Notes</dt><dd class="text-espresso/80">{{ $order->special_notes }}</dd></div>
                    @endif
                </dl>
            </div>

            @if ($order->payment_method === 'bank_transfer')
                <div class="card p-6">
                    <h3 class="font-serif text-lg text-espresso">Bank Transfer Receipt</h3>
                    @if ($order->receiptUrl())
                        <a href="{{ $order->receiptUrl() }}" target="_blank" class="mt-3 block">
                            <img src="{{ $order->receiptUrl() }}" alt="Payment receipt" class="max-h-96 rounded-lg border border-blush">
                        </a>
                    @else
                        <p class="mt-2 text-sm text-espresso/60">No receipt was uploaded.</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h3 class="font-serif text-lg text-espresso">Customer</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div><dt class="font-semibold text-espresso">Name</dt><dd class="text-espresso/80">{{ $order->name }}</dd></div>
                    <div><dt class="font-semibold text-espresso">Phone</dt><dd class="text-espresso/80">{{ $order->phone }}</dd></div>
                    @if ($order->email)
                        <div><dt class="font-semibold text-espresso">Email</dt><dd class="text-espresso/80">{{ $order->email }}</dd></div>
                    @endif
                    <div><dt class="font-semibold text-espresso">Payment Method</dt><dd class="text-espresso/80">{{ $order->paymentMethodLabel() }}</dd></div>
                </dl>
            </div>

            <div class="card p-6">
                <h3 class="font-serif text-lg text-espresso">Update Status</h3>
                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-3 space-y-3">
                    @csrf @method('PATCH')
                    <select name="status" class="input w-full">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ order_status_label($status) }}</option>
                        @endforeach
                        <option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option>
                    </select>
                    <button type="submit" class="btn-primary w-full">Update Status</button>
                </form>
                @if ($order->status === 'cancelled')
                    <p class="mt-2 text-xs text-espresso/60">Cancelled {{ $order->cancelled_at?->format('d M Y, g:ia') }} — stock was restored.</p>
                @endif
            </div>
        </div>
    </div>

</x-app-layout>
