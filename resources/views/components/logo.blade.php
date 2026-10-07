@props([
    'link' => true,
    'size' => 'md',
    'for' => 'web',
    'class' => '',
    'dark' => false,
])

{{--
    The ONE place the Aurelia Boutonnière logo is rendered. Every header,
    footer, admin sidebar, email, and invoice uses <x-logo /> so swapping
    in the real logo later is a single change: upload it on the admin
    Settings screen and this component renders the uploaded file instead
    of the text placeholder everywhere it appears. (The browser favicon
    is the one exception — see resources/views/partials/favicon.blade.php
    — because <link rel="icon"> has to live in <head>, not inline markup.)

    - for="web"   (default) — Tailwind-classed markup for the live site.
    - for="email" — table/inline-style markup safe for email clients,
                    which strip <style> blocks and often lack web fonts.
--}}
@php
    $logoPath = setting('logo_path');
    $logoUrl = $logoPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) : null;
    $shopName = setting('shop_name', config('shop.name'));

    $sizes = [
        'sm' => ['script' => 'text-xl', 'serif' => 'text-xs', 'img' => 'h-8', 'scriptPx' => 20, 'serifPx' => 11, 'imgPx' => 32],
        'md' => ['script' => 'text-3xl', 'serif' => 'text-base', 'img' => 'h-11', 'scriptPx' => 30, 'serifPx' => 15, 'imgPx' => 44],
        'lg' => ['script' => 'text-4xl', 'serif' => 'text-xl', 'img' => 'h-16', 'scriptPx' => 40, 'serifPx' => 19, 'imgPx' => 64],
        'xl' => ['script' => 'text-5xl', 'serif' => 'text-2xl', 'img' => 'h-20', 'scriptPx' => 52, 'serifPx' => 23, 'imgPx' => 80],
    ];
    $s = $sizes[$size] ?? $sizes['md'];

    $scriptColor = $dark ? '#F5F0E6' : '#8B6F4E'; // mocha, or cream when on a dark surface
    $serifColor = $dark ? '#F5F0E6' : '#3E3228'; // espresso, or cream when on a dark surface
@endphp

@if ($for === 'email')
    {{-- Inline-only markup (no <table>/<div>): this gets wrapped inside the
         mail layout's <a> tag, and block-level content there gets hoisted
         outside the link by the CSS-inliner's DOM parser, breaking the
         "logo links home" behaviour. Plain inline elements nest safely. --}}
    @if ($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $shopName }}" height="{{ $s['imgPx'] }}" style="height:{{ $s['imgPx'] }}px;width:auto;border:0;vertical-align:middle;">
    @else
        <span style="font-family: Georgia, 'Times New Roman', serif; white-space:nowrap;">
            <span style="font-size:{{ $s['scriptPx'] }}px; font-style:italic; color:{{ $scriptColor }};">Aurelia</span>
            <span style="font-size:{{ $s['serifPx'] }}px; letter-spacing:0.05em; color:{{ $serifColor }}; margin-left:4px;">Boutonni&egrave;re</span>
        </span>
    @endif
@else
    @php $tag = $link ? 'a' : 'span'; @endphp
    <{{ $tag }}
        @if ($link) href="{{ route('home') }}" @endif
        {{ $attributes->class(['inline-flex items-baseline gap-2 select-none', $class]) }}
        aria-label="{{ $shopName }} — home"
    >
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $shopName }}" class="{{ $s['img'] }} w-auto object-contain">
        @else
            <span class="font-script {{ $s['script'] }} leading-none {{ $dark ? 'text-beige' : 'text-mocha' }}">Aurelia</span>
            <span class="font-serif {{ $s['serif'] }} tracking-wide leading-none {{ $dark ? 'text-beige' : 'text-espresso' }}">Boutonni&egrave;re</span>
        @endif
    </{{ $tag }}>
@endif
