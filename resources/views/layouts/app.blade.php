<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $title ?? 'Dashboard' }} — {{ setting('shop_name', config('shop.name')) }} Admin</title>
        @include('partials.favicon')

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Great+Vibes&family=Lato:wght@400;500;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans text-espresso antialiased bg-beige">
        <div x-data="{ sidebarOpen: false }" class="flex min-h-screen">

            {{-- Mobile overlay --}}
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
                 class="fixed inset-0 z-30 bg-espresso/40 lg:hidden"></div>

            {{-- Sidebar --}}
            <aside
                x-show="sidebarOpen || window.innerWidth >= 1024"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                class="fixed inset-y-0 left-0 z-40 w-64 flex-shrink-0 overflow-y-auto border-r border-blush bg-beige-surface lg:static lg:block"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            >
                <div class="flex h-20 items-center justify-center border-b border-blush px-4">
                    <a href="{{ route('admin.dashboard') }}">
                        <x-logo size="sm" :link="false" />
                    </a>
                </div>

                <nav class="space-y-1 px-3 py-6">
                    @php
                        $navItems = [
                            ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
                            ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => 'Products', 'icon' => 'flower'],
                            ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Categories', 'icon' => 'tag'],
                            ['route' => 'admin.flowers.index', 'pattern' => 'admin.flowers.*', 'label' => 'Flowers', 'icon' => 'sparkles'],
                            ['route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'label' => 'Orders', 'icon' => 'box'],
                            ['route' => 'admin.messages.index', 'pattern' => 'admin.messages.*', 'label' => 'Messages', 'icon' => 'mail'],
                            ['route' => 'admin.settings.edit', 'pattern' => 'admin.settings.*', 'label' => 'Settings', 'icon' => 'cog'],
                        ];
                    @endphp

                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition
                                  {{ request()->routeIs($item['pattern']) ? 'bg-sand text-beige-surface shadow-soft' : 'text-espresso hover:bg-blush' }}">
                            <x-icon :name="$item['icon']" class="h-5 w-5 flex-shrink-0" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="mt-auto border-t border-blush px-4 py-4">
                    <p class="truncate text-sm font-semibold text-espresso">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-mocha">{{ auth()->user()->email }}</p>
                    <div class="mt-3 flex items-center gap-3 text-xs">
                        <a href="{{ route('profile.edit') }}" class="text-mocha hover:underline">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-mocha hover:underline">Log out</button>
                        </form>
                    </div>
                </div>
            </aside>

            {{-- Main column --}}
            <div class="flex min-w-0 flex-1 flex-col">
                {{-- Topbar --}}
                <header class="flex h-20 items-center justify-between border-b border-blush bg-beige-surface px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-espresso lg:hidden" aria-label="Toggle menu">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="font-serif text-xl sm:text-2xl text-espresso">{{ $title ?? 'Dashboard' }}</h1>
                    </div>

                    <a href="{{ route('home') }}" target="_blank" class="hidden text-sm font-semibold text-mocha hover:underline sm:inline">
                        View storefront &rarr;
                    </a>
                </header>

                <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
                    @if (session('success'))
                        <div class="mb-6 rounded-lg bg-sand/20 border border-sand px-4 py-3 text-sm font-semibold text-espresso">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 rounded-lg bg-red-100 border border-red-300 px-4 py-3 text-sm font-semibold text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
