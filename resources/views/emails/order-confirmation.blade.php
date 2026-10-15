<x-mail::message>
# Thank you for your order, {{ $order->name }}!

Your order has been received and our florists are already preparing it.

**Order Number:** {{ $order->order_number }}
**Delivery:** {{ $order->delivery_date->format('d M Y') }} &middot; {{ $order->delivery_time_slot }}
**Payment Method:** {{ $order->paymentMethodLabel() }}

<div class="table">
<table>
<thead>
<tr>
<th align="left">Item</th>
<th align="center">Qty</th>
<th align="right">Total</th>
</tr>
</thead>
<tbody>
@foreach ($order->items as $item)
<tr>
<td align="left">
{{ $item->product_title }}
@if ($item->wrapping_paper_name || $item->ribbon_name)
<br><small>{{ collect([
    $item->wrapping_paper_name ? "Wrapped in {$item->wrapping_paper_name}" : null,
    $item->ribbon_name ? "{$item->ribbon_name} ribbon" : null,
])->filter()->implode(' &middot; ') }}</small>
@endif
</td>
<td align="center">{{ $item->quantity }}</td>
<td align="right"><x-price :amount="$item->line_total" for="image" /></td>
</tr>
@endforeach
</tbody>
</table>
</div>

<div class="table">
<table>
<tbody>
<tr><td align="left">Subtotal</td><td align="right"><x-price :amount="$order->subtotal" for="image" /></td></tr>
<tr><td align="left">Delivery Fee</td><td align="right"><x-price :amount="$order->delivery_fee" for="image" /></td></tr>
@foreach ($order->charges as $charge)
<tr><td align="left">{{ $charge->name }}</td><td align="right"><x-price :amount="$charge->amount" for="image" /></td></tr>
@endforeach
<tr><td align="left"><strong>Total</strong></td><td align="right"><strong><x-price :amount="$order->total" for="image" /></strong></td></tr>
</tbody>
</table>
</div>

**Delivering to:** {{ $order->delivery_address }}

@if ($order->gift_message)
**Gift Message:** "{{ $order->gift_message }}"
@endif

@if ($order->special_notes)
**Notes:** {{ $order->special_notes }}
@endif

<x-mail::button :url="route('orders.track.show')">
Track Your Order
</x-mail::button>

If you have any questions about your order, simply reply to this email — we're happy to help.

With gratitude,<br>
{{ setting('shop_name', config('shop.name')) }}
</x-mail::message>
