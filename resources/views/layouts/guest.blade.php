<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ setting('shop_name', config('shop.name')) }} — Admin</title>
        @include('partials.favicon')

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Great+Vibes&family=Lato:wght@400;500;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-espresso antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-beige px-4 py-10">
            <a href="{{ route('home') }}" class="mb-8">
                <x-logo size="lg" :link="false" />
            </a>

            <div class="w-full max-w-md rounded-xl2 bg-beige-surface px-8 py-10 shadow-soft">
                {{ $slot }}
            </div>

            <p class="mt-8 text-sm text-mocha">
                <a href="{{ route('home') }}" class="hover:underline">&larr; Back to the shop</a>
            </p>
        </div>
    </body>
</html>
