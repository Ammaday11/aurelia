<x-app-layout title="Products">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products..." class="input w-56">
            <select name="category" class="input" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-outline">Filter</button>
        </form>

        <a href="{{ route('admin.products.create') }}" class="btn-primary">+ New Product</a>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-blush text-xs uppercase tracking-wide text-mocha">
                    <th class="px-5 py-3">Product</th>
                    <th class="px-5 py-3">Category</th>
                    <th class="px-5 py-3">Price</th>
                    <th class="px-5 py-3">Stock</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-blush">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-lg bg-blush">
                                    @if ($image = $product->images->first())
                                        <img src="{{ $image->thumbUrl() }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-mocha/50"><x-icon name="flower" class="h-5 w-5" /></div>
                                    @endif
                                </div>
                                <span class="font-semibold text-espresso">{{ $product->title }}</span>
                                @if ($product->is_featured)
                                    <span class="badge bg-sand/30 text-espresso">Featured</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3 text-espresso/80">{{ $product->category?->name }}</td>
                        <td class="px-5 py-3">
                            @if ($product->is_on_sale)
                                <x-price :amount="$product->sale_price" class="text-mocha" />
                                <span class="price-rufiyaa block text-xs text-espresso/50 line-through">{{ money($product->price) }}</span>
                            @else
                                <x-price :amount="$product->price" />
                            @endif
                        </td>
                        <td class="px-5 py-3 text-espresso/80">{{ $product->stock_quantity }}</td>
                        <td class="px-5 py-3">
                            @if (! $product->is_active)
                                <span class="badge bg-blush text-espresso/60">Hidden</span>
                            @elseif ($product->stock_quantity <= 0)
                                <span class="badge bg-red-100 text-red-700">Out of Stock</span>
                            @elseif ($product->is_low_stock)
                                <span class="badge bg-mocha/20 text-mocha">Low Stock</span>
                            @else
                                <span class="badge bg-sand/30 text-espresso">Active</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-mocha hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                      onsubmit="return confirm('Delete this product? This cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-espresso/60">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $products->links() }}
    </div>

</x-app-layout>
