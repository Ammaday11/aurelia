<x-storefront-layout>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-blush/40">
        <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-sand/30 blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-mocha/20 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="font-script text-4xl text-mocha sm:text-5xl">Flowers, artfully arranged</p>
                <h1 class="mt-4 font-serif text-4xl text-espresso sm:text-5xl lg:text-6xl">
                    Hand-tied bouquets, delivered fresh across the Maldives
                </h1>
                <p class="mx-auto mt-6 max-w-xl text-base leading-relaxed text-espresso/80 sm:text-lg">
                    {{ setting('hero_tagline', 'Every arrangement is crafted to order — fresh, elegant, and ready to brighten someone\'s day.') }}
                </p>
                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ route('shop.index') }}" class="btn-primary">Shop the Collection</a>
                    <a href="{{ route('pages.about') }}" class="btn-outline">Our Story</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Featured bouquets --}}
    @if ($featured->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <p class="section-tagline">Customer favourites</p>
            <h2 class="section-title mt-2">Featured Bouquets</h2>
            <div class="floral-divider my-6"></div>

            <div class="mt-10 grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($featured as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('shop.index') }}" class="btn-secondary">View Full Shop</a>
            </div>
        </section>
    @endif

    {{-- Categories --}}
    @if ($categories->isNotEmpty())
        <section class="bg-beige-surface py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="section-tagline">Shop by</p>
                <h2 class="section-title mt-2">Categories</h2>
                <div class="floral-divider my-6"></div>

                <div class="mt-10 grid grid-cols-2 gap-5 sm:grid-cols-4">
                    @foreach ($categories as $category)
                        <a href="{{ route('shop.index', ['category' => $category->slug]) }}"
                           class="card flex flex-col items-center gap-3 px-4 py-10 text-center">
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-blush text-mocha">
                                <x-icon name="flower" class="h-8 w-8" />
                            </span>
                            <span class="font-serif text-lg text-espresso">{{ $category->name }}</span>
                            <span class="text-xs text-mocha">{{ $category->active_products_count }} {{ Str::plural('bouquet', $category->active_products_count) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Why choose us --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <p class="section-tagline">Why we're different</p>
        <h2 class="section-title mt-2">Why Choose Aurelia</h2>
        <div class="floral-divider my-6"></div>

        <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $reasons = [
                    ['icon' => 'flower', 'title' => 'Fresh & Handcrafted', 'body' => 'Every stem is selected and arranged by hand the same day it ships.'],
                    ['icon' => 'box', 'title' => 'Careful Delivery', 'body' => 'Arrangements are packaged to arrive exactly as they left our studio.'],
                    ['icon' => 'check', 'title' => 'Made to Order', 'body' => 'Choose your time slot and we\'ll have it ready, right on schedule.'],
                    ['icon' => 'mail', 'title' => 'A Personal Touch', 'body' => 'Add a gift message and we\'ll handwrite it onto the card for you.'],
                ];
            @endphp
            @foreach ($reasons as $reason)
                <div class="text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-sand/20 text-mocha">
                        <x-icon :name="$reason['icon']" class="h-7 w-7" />
                    </span>
                    <h3 class="mt-4 font-serif text-lg text-espresso">{{ $reason['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-espresso/70">{{ $reason['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Testimonials --}}
    @if (! empty($testimonials))
        <section class="bg-blush/30 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="section-tagline">Kind words</p>
                <h2 class="section-title mt-2">What Our Customers Say</h2>
                <div class="floral-divider my-6"></div>

                <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-3">
                    @foreach ($testimonials as $t)
                        <div class="card p-6">
                            <div class="flex gap-0.5 text-sand" aria-hidden="true">
                                @for ($i = 0; $i < $t['rating']; $i++)
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.6 6.1.6-4.6 4.2 1.3 6-5.4-3-5.4 3 1.3-6L1.3 7.7l6.1-.6z"/></svg>
                                @endfor
                            </div>
                            <p class="mt-4 text-sm leading-relaxed text-espresso/85">&ldquo;{{ $t['quote'] }}&rdquo;</p>
                            <p class="mt-4 font-serif text-espresso">{{ $t['name'] }}</p>
                            <p class="text-xs text-mocha">{{ $t['location'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-storefront-layout>
