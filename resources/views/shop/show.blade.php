<x-storefront-layout :title="$product->title" :description="\Illuminate\Support\Str::limit(strip_tags($product->description), 160)">

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <nav class="mb-8 text-sm text-mocha">
            <a href="{{ route('shop.index') }}" class="hover:underline">Shop</a>
            @if ($product->category)
                <span class="mx-2">/</span>
                <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="hover:underline">{{ $product->category->name }}</a>
            @endif
            <span class="mx-2">/</span>
            <span class="text-espresso/70">{{ $product->title }}</span>
        </nav>

        <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">

            {{-- Gallery --}}
            <div x-data="{ active: 0, images: {{ $product->images->map(fn ($image) => $image->url())->toJson() }} }">
                <div class="aspect-square overflow-hidden rounded-xl2 bg-blush shadow-card">
                    @if ($product->images->isNotEmpty())
                        <template x-if="images.length">
                            <img :src="images[active]" alt="{{ $product->title }}" class="h-full w-full object-cover">
                        </template>
                    @else
                        <div class="flex h-full w-full items-center justify-center text-mocha/50">
                            <x-icon name="flower" class="h-24 w-24" />
                        </div>
                    @endif
                </div>

                @if ($product->images->count() > 1)
                    <div class="mt-4 grid grid-cols-5 gap-3">
                        @foreach ($product->images as $i => $image)
                            <button @click="active = {{ $i }}"
                                    :class="active === {{ $i }} ? 'ring-2 ring-mocha' : 'ring-1 ring-blush'"
                                    class="aspect-square overflow-hidden rounded-lg transition">
                                <img src="{{ $image->thumbUrl() }}" alt="{{ $product->title }} thumbnail {{ $i + 1 }}" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div>
                @if ($product->category)
                    <p class="text-sm font-semibold uppercase tracking-wide text-mocha">{{ $product->category->name }}</p>
                @endif
                <h1 class="mt-2 font-serif text-3xl text-espresso sm:text-4xl">{{ $product->title }}</h1>

                <div class="mt-4 flex items-baseline gap-3">
                    @if ($product->is_on_sale)
                        <x-price :amount="$product->sale_price" class="text-2xl font-semibold text-mocha" />
                        <s class="price-rufiyaa text-lg text-espresso/50">{{ money($product->price) }}</s>
                        <span class="badge bg-mocha text-beige-surface">-{{ $product->discount_percent }}%</span>
                    @else
                        <x-price :amount="$product->price" class="text-2xl font-semibold text-espresso" />
                    @endif
                </div>

                <p class="mt-3 text-sm font-semibold {{ $product->in_stock ? ($product->is_low_stock ? 'text-mocha' : 'text-green-700') : 'text-red-600' }}">
                    {{ $product->stock_status_label }}
                </p>

                @if ($product->description)
                    <div class="prose prose-sm mt-6 max-w-none text-espresso/85 prose-headings:font-serif prose-headings:text-espresso">
                        {!! $product->description !!}
                    </div>
                @endif

                @if ($product->in_stock)
                    <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-8 space-y-5 border-t border-blush pt-8">
                        @csrf

                        <div>
                            <label for="quantity" class="label">Quantity</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ min($product->stock_quantity, 50) }}"
                                   class="input w-28">
                        </div>

                        <div>
                            <label for="gift_message" class="label">Gift Message (optional)</label>
                            <textarea id="gift_message" name="gift_message" rows="3" maxlength="300"
                                      placeholder="e.g. Happy Birthday! With love, ..."
                                      class="input w-full"></textarea>
                        </div>

                        <button type="submit" class="btn-primary w-full sm:w-auto">
                            <x-icon name="cart" class="h-5 w-5" /> Add to Cart
                        </button>
                    </form>
                @else
                    <div class="mt-8 border-t border-blush pt-8">
                        <p class="text-sm text-espresso/70">This bouquet is currently out of stock. Check back soon, or browse similar arrangements below.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Related products --}}
        @if ($related->isNotEmpty())
            <div class="mt-20">
                <h2 class="font-serif text-2xl text-espresso">You May Also Like</h2>
                <div class="floral-divider my-6"></div>
                <div class="grid grid-cols-2 gap-5 sm:grid-cols-4">
                    @foreach ($related as $relatedProduct)
                        <x-product-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </div>
        @endif
    </section>

</x-storefront-layout>
