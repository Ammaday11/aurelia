@props(['item'])

{{--
    The flower/wrap/ribbon breakdown sub-lines for a custom-bouquet
    OrderItem, reused everywhere a placed order's items are listed
    (confirmation, tracking, admin order detail, invoice, email). Not for
    the live cart/checkout — those render App\Services\CartService's
    resolved objects (hydrated Flower/WrappingPaper/Ribbon models), a
    different shape from OrderItem's flat snapshot columns.
--}}
@if ($item->isCustom())
    <p class="text-xs text-espresso/60">
        {{ collect($item->custom_flowers)->map(fn ($f) => "{$f['quantity']}\u{00D7} {$f['name']}")->implode(', ') }}
    </p>
    @if ($item->wrapping_paper_name || $item->ribbon_name)
        <p class="text-xs text-espresso/60">
            {{ collect([
                $item->wrapping_paper_name ? "Wrapped in {$item->wrapping_paper_name}" : null,
                $item->ribbon_name ? "{$item->ribbon_name} ribbon" : null,
            ])->filter()->implode(' · ') }}
        </p>
    @endif
    @if ($item->referenceImageUrl())
        <a href="{{ $item->referenceImageUrl() }}" target="_blank" class="mt-1 inline-block">
            <img src="{{ $item->referenceImageUrl() }}" alt="Reference photo" class="h-14 w-14 rounded-lg border border-blush object-cover">
        </a>
    @endif
@endif
