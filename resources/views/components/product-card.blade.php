@props(['product'])

<a href="{{ route('shop.show', $product) }}" class="card group block overflow-hidden">
    <div class="relative aspect-square overflow-hidden bg-blush">
        @if ($image = $product->images->first())
            <img src="{{ $image->thumbUrl() }}" alt="{{ $product->title }}"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        @else
            <div class="flex h-full w-full items-center justify-center text-mocha/50">
                <x-icon name="flower" class="h-16 w-16" />
            </div>
        @endif

        <div class="absolute left-3 top-3 flex flex-col gap-1.5">
            @if ($product->is_on_sale)
                <span class="badge bg-mocha text-beige-surface">-{{ $product->discount_percent }}%</span>
            @endif
            @if ($product->is_featured)
                <span class="badge bg-sand text-beige-surface">Featured</span>
            @endif
        </div>

        @unless ($product->in_stock)
            <div class="absolute inset-0 flex items-center justify-center bg-espresso/50">
                <span class="badge bg-beige-surface text-espresso">Out of Stock</span>
            </div>
        @endunless
    </div>

    <div class="p-5">
        @if ($product->category)
            <p class="text-xs font-semibold uppercase tracking-wide text-mocha">{{ $product->category->name }}</p>
        @endif
        <h3 class="mt-1 font-serif text-lg text-espresso line-clamp-1">{{ $product->title }}</h3>

        <div class="mt-2 flex items-baseline gap-2">
            @if ($product->is_on_sale)
                <x-price :amount="$product->sale_price" class="text-base font-semibold text-mocha" />
                <s class="price-rufiyaa text-sm text-espresso/50">{{ money($product->price) }}</s>
            @else
                <x-price :amount="$product->price" class="text-base font-semibold text-espresso" />
            @endif
        </div>
    </div>
</a>
