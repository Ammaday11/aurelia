@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
])

@php
    $shopName = setting('shop_name', config('shop.name'));
    $pageTitle = $title ? "{$title} — {$shopName}" : "{$shopName} — ".config('shop.tagline');
    $pageDescription = $description ?: setting('meta_description', 'Fresh, hand-tied bouquets and flower arrangements in the Maldives, delivered with care. Order online from Aurelia Boutonnière.');
    $pageImage = $image ?: asset('apple-touch-icon.png');
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
<link rel="canonical" href="{{ url()->current() }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:image" content="{{ $pageImage }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="{{ $shopName }}">
<meta property="og:locale" content="en_US">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $pageImage }}">
