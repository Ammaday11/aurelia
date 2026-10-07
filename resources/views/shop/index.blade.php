<x-storefront-layout title="Shop" description="Browse fresh bouquets and flower arrangements from Aurelia Boutonnière, delivered across the Maldives.">

    <section class="bg-blush/30 py-12">
        <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
            <p class="font-script text-3xl text-mocha">Our Collection</p>
            <h1 class="mt-2 font-serif text-4xl text-espresso">Shop Flowers</h1>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-4">

            {{-- Filters --}}
            <aside class="lg:col-span-1">
                <form method="GET" action="{{ route('shop.index') }}" class="card space-y-6 p-6">
                    <div>
                        <label for="q" class="label">Search</label>
                        <div class="relative">
                            <input type="text" id="q" name="q" value="{{ $search }}" placeholder="Search bouquets..."
                                   class="input pr-10">
                            <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-mocha" />
                        </div>
                    </div>

                    <div>
                        <span class="label">Category</span>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-sm text-espresso">
                                <input type="radio" name="category" value="" class="text-mocha focus:ring-sand" {{ $activeCategory ? '' : 'checked' }}>
                                All Categories
                            </label>
                            @foreach ($categories as $category)
                                <label class="flex items-center gap-2 text-sm text-espresso">
                                    <input type="radio" name="category" value="{{ $category->slug }}" class="text-mocha focus:ring-sand"
                                           {{ $activeCategory === $category->slug ? 'checked' : '' }}>
                                    {{ $category->name }}
                                    <span class="text-xs text-mocha">({{ $category->active_products_count }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="sort" class="label">Sort By</label>
                        <select id="sort" name="sort" class="input">
                            <option value="newest" @selected($sort === 'newest')>Newest</option>
                            <option value="price_asc" @selected($sort === 'price_asc')>Price: Low to High</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>Price: High to Low</option>
                            <option value="name_asc" @selected($sort === 'name_asc')>Name: A to Z</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary w-full">Apply Filters</button>
                    @if ($activeCategory || $search || $sort !== 'newest')
                        <a href="{{ route('shop.index') }}" class="block text-center text-sm text-mocha hover:underline">Clear filters</a>
                    @endif
                </form>
            </aside>

            {{-- Grid --}}
            <div class="lg:col-span-3">
                <div class="mb-6 flex items-center justify-between">
                    <p class="text-sm text-espresso/70">{{ $products->total() }} {{ Str::plural('bouquet', $products->total()) }} found</p>
                </div>

                @if ($products->isEmpty())
                    <div class="card p-12 text-center">
                        <p class="font-serif text-xl text-espresso">No bouquets matched your search.</p>
                        <a href="{{ route('shop.index') }}" class="mt-4 inline-block text-mocha hover:underline">Browse the full shop &rarr;</a>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-5 sm:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>

</x-storefront-layout>
