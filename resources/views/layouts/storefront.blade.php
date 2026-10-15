<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-seo :title="$title" :description="$description" :image="$image" />
        @include('partials.favicon')

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Cormorant+Garamond:wght@500;600&family=Great+Vibes&family=Lato:wght@400;500;700;900&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        {{ $head ?? '' }}
    </head>
    <body class="flex min-h-screen flex-col bg-beige font-sans text-espresso antialiased">

        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-mocha focus:px-4 focus:py-2 focus:text-beige-surface">
            Skip to content
        </a>

        <header x-data="{ mobileOpen: false }" class="sticky top-0 z-40 border-b border-blush/70 bg-beige/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <x-logo size="lg" />

                <nav class="hidden items-center gap-8 lg:flex">
                    @php
                        $navLinks = [
                            ['route' => 'home', 'label' => 'Home'],
                            ['route' => 'shop.index', 'label' => 'Shop'],
                            ['route' => 'custom-bouquet', 'label' => 'Custom Bouquet'],
                            ['route' => 'pages.about', 'label' => 'About'],
                            ['route' => 'pages.contact', 'label' => 'Contact'],
                            ['route' => 'orders.track.show', 'label' => 'Track Order'],
                        ];
                    @endphp
                    @foreach ($navLinks as $link)
                        <a href="{{ route($link['route']) }}"
                           class="font-sans text-sm font-semibold uppercase tracking-wide transition
                                  {{ request()->routeIs($link['route']) || ($link['route'] === 'shop.index' && request()->routeIs('shop.show')) ? 'text-mocha' : 'text-espresso hover:text-mocha' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-4">
                    <a href="{{ route('cart.index') }}" class="relative flex items-center text-espresso hover:text-mocha" aria-label="View cart">
                        <x-icon name="cart" class="h-7 w-7" />
                        @php $cartCount = app('cart')->count(); @endphp
                        @if ($cartCount > 0)
                            <span class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-mocha text-[11px] font-bold text-beige-surface">
                                {{ $cartCount > 9 ? '9+' : $cartCount }}
                            </span>
                        @endif
                    </a>

                    <button @click="mobileOpen = !mobileOpen" class="text-espresso lg:hidden" aria-label="Toggle menu">
                        <x-icon name="menu" x-show="!mobileOpen" class="h-7 w-7" />
                        <x-icon name="close" x-show="mobileOpen" x-cloak class="h-7 w-7" />
                    </button>
                </div>
            </div>

            {{-- Mobile nav --}}
            <div x-show="mobileOpen" x-cloak x-transition
                 class="border-t border-blush/70 bg-beige-surface px-4 py-4 lg:hidden">
                <nav class="flex flex-col gap-3">
                    @foreach ($navLinks as $link)
                        <a href="{{ route($link['route']) }}" @click="mobileOpen = false"
                           class="font-sans text-base font-semibold {{ request()->routeIs($link['route']) ? 'text-mocha' : 'text-espresso' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </header>

        @if (session('success'))
            <div class="bg-sand/20 border-b border-sand px-4 py-3 text-center text-sm font-semibold text-espresso">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="border-b border-red-300 bg-red-100 px-4 py-3 text-center text-sm font-semibold text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <main id="main-content" class="flex-1">
            {{ $slot }}
        </main>

        <footer class="mt-20 border-t border-blush/70 bg-beige-surface">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <x-logo size="lg" />
                        <p class="mt-4 max-w-xs text-sm leading-relaxed text-espresso/80">
                            {{ setting('footer_tagline', 'Hand-tied bouquets and thoughtful arrangements, delivered fresh across the Maldives.') }}
                        </p>
                    </div>

                    <div>
                        <h3 class="font-serif text-lg text-espresso">Explore</h3>
                        <ul class="mt-4 space-y-2 text-sm text-espresso/80">
                            <li><a href="{{ route('shop.index') }}" class="hover:text-mocha">Shop</a></li>
                            <li><a href="{{ route('pages.about') }}" class="hover:text-mocha">About Us</a></li>
                            <li><a href="{{ route('pages.contact') }}" class="hover:text-mocha">Contact</a></li>
                            <li><a href="{{ route('orders.track.show') }}" class="hover:text-mocha">Track Your Order</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="font-serif text-lg text-espresso">Get in Touch</h3>
                        <ul class="mt-4 space-y-2 text-sm text-espresso/80">
                            @if ($phone = setting('contact_phone'))
                                <li><a href="tel:{{ $phone }}" class="hover:text-mocha">{{ $phone }}</a></li>
                            @endif
                            @if ($email = setting('contact_email'))
                                <li><a href="mailto:{{ $email }}" class="hover:text-mocha">{{ $email }}</a></li>
                            @endif
                            @if ($address = setting('contact_address'))
                                <li>{{ $address }}</li>
                            @endif
                        </ul>
                    </div>

                    <div>
                        <h3 class="font-serif text-lg text-espresso">Follow Along</h3>
                        <ul class="mt-4 flex gap-4 text-sm text-espresso/80">
                            @if ($fb = setting('social_facebook'))
                                <li><a href="{{ $fb }}" target="_blank" rel="noopener" class="hover:text-mocha">Facebook</a></li>
                            @endif
                            @if ($ig = setting('social_instagram'))
                                <li><a href="{{ $ig }}" target="_blank" rel="noopener" class="hover:text-mocha">Instagram</a></li>
                            @endif
                            @if ($tt = setting('social_tiktok'))
                                <li><a href="{{ $tt }}" target="_blank" rel="noopener" class="hover:text-mocha">TikTok</a></li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="floral-divider my-10 opacity-70"></div>

                <p class="text-center text-xs text-espresso/60">
                    &copy; {{ now()->year }} {{ setting('shop_name', config('shop.name')) }}. All rights reserved.
                </p>
            </div>
        </footer>

        @livewireScripts
        {{ $scripts ?? '' }}
    </body>
</html>
