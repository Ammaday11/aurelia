@props(['status'])

@php
    $styles = match ($status) {
        'pending' => 'bg-blush text-espresso',
        'confirmed' => 'bg-sand/30 text-espresso',
        'preparing' => 'bg-sand text-beige-surface',
        'out_for_delivery' => 'bg-mocha text-beige-surface',
        'delivered' => 'bg-green-600 text-white',
        'cancelled' => 'bg-red-100 text-red-700',
        default => 'bg-blush text-espresso',
    };
@endphp

<span {{ $attributes->class(['badge', $styles]) }}>{{ order_status_label($status) }}</span>
