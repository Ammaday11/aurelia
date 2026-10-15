@props(['order', 'for' => 'web', 'rowClass' => 'text-espresso/80'])

{{--
    Subtotal / Delivery Fee / each attached charge, for a placed Order —
    reused on the confirmation page, order tracking, the admin order
    detail page, the printable invoice, and the confirmation email. The
    caller adds its own Total row after this (amount, styling, and
    whether it's inside the same wrapping div all differ slightly per
    page). $order->charges loads lazily if not already eager-loaded.

    Delivery fee is no longer its own column going forward — it's just an
    automatic OrderCharge like any other (see OrderService::placeOrder()),
    so it already appears via the @foreach below. $order->delivery_fee is
    only ever non-zero on an order placed before that move, so this
    dedicated row is purely for those older orders.
--}}
<div class="flex justify-between {{ $rowClass }}"><span>Subtotal</span><x-price :amount="$order->subtotal" :for="$for" /></div>
@if ($order->delivery_fee > 0)
    <div class="flex justify-between {{ $rowClass }}"><span>Delivery Fee</span><x-price :amount="$order->delivery_fee" :for="$for" /></div>
@endif
@foreach ($order->charges as $charge)
    <div class="flex justify-between {{ $rowClass }}"><span>{{ $charge->name }}</span><x-price :amount="$charge->amount" :for="$for" /></div>
@endforeach
