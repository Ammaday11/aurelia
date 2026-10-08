{{--
    Favicon tags. <link rel="icon"> has to live in <head>, so this can't be
    the <x-logo /> component itself — but it draws from the exact same
    Settings-managed "favicon_path" (admin Settings > Logo & Favicon), with
    a bundled default so the tab icon never 404s before one is uploaded.
--}}
@php
    $faviconPath = setting('favicon_path');
    // asset(), not Storage::disk('public')->url() (which hardcodes
    // APP_URL) — see App\Models\ProductImage::url() for why.
    $faviconUrl = $faviconPath ? asset('storage/'.$faviconPath) : null;
@endphp

@if ($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
@else
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
@endif
