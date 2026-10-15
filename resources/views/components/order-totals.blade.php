@props(['order', 'for' => 'web', 'rowClass' => 'text-espresso/80'])

{{--
    Subtotal / Delivery Fee / each attached charge, for a placed Order —
    reused on the confirmation page, order tracking, the admin order
    detail page, the printable invoice, and the confirmation email. The
    caller adds its own Total row after this (amount, styling, and
    whether it's inside the same wrapping div all differ slightly per
    page). $order->charges loads lazily if not already eager-loaded.
--}}
<div class="flex justify-between {{ $rowClass }}"><span>Subtotal</span><x-price :amount="$order->subtotal" :for="$for" /></div>
<div class="flex justify-between {{ $rowClass }}"><span>Delivery Fee</span><x-price :amount="$order->delivery_fee" :for="$for" /></div>
@foreach ($order->charges as $charge)
    <div class="flex justify-between {{ $rowClass }}"><span>{{ $charge->name }}</span><x-price :amount="$charge->amount" :for="$for" /></div>
@endforeach
