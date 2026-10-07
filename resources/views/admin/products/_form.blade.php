@csrf
@isset($product) @method('PUT') @endisset

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="card space-y-5 p-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="title" value="Product Title" />
                    <x-text-input id="title" name="title" class="mt-1 w-full" value="{{ old('title', $product->title ?? '') }}" required />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="sku" value="SKU (optional)" />
                    <x-text-input id="sku" name="sku" class="mt-1 w-full" value="{{ old('sku', $product->sku ?? '') }}" />
                    <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="slug" value="Slug (optional — auto-generated if left blank)" />
                <x-text-input id="slug" name="slug" class="mt-1 w-full" value="{{ old('slug', $product->slug ?? '') }}" />
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="Description" />
                <input id="description" name="description" type="hidden" value="{{ old('description', $product->description ?? '') }}">
                <trix-editor input="description" class="mt-1"></trix-editor>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>

        {{-- Images --}}
        <div class="card space-y-5 p-6">
            <x-input-label value="Product Images" />

            @isset($product)
                @if ($product->images->isNotEmpty())
                    <p class="text-xs text-espresso/60">Drag to reorder. The first image is used as the main thumbnail.</p>
                    <div id="image-sort-list" data-reorder-url="{{ route('admin.products.images.reorder', $product) }}" class="grid grid-cols-3 gap-4 sm:grid-cols-4">
                        @foreach ($product->images as $image)
                            <div class="group relative cursor-move rounded-lg border border-blush" draggable="true" data-image-id="{{ $image->id }}">
                                <img src="{{ $image->thumbUrl() }}" alt="" class="aspect-square w-full rounded-lg object-cover">
                                <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}"
                                      onsubmit="return confirm('Remove this image?');"
                                      class="absolute right-1 top-1">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="flex h-7 w-7 items-center justify-center rounded-full bg-espresso/70 text-beige-surface opacity-0 transition group-hover:opacity-100">
                                        <x-icon name="close" class="h-4 w-4" />
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endisset

            <div>
                <x-input-label for="images" value="Upload New Images" />
                <input type="file" id="images" name="images[]" accept="image/*" multiple
                       class="input mt-1 w-full file:mr-4 file:rounded-full file:border-0 file:bg-mocha file:px-4 file:py-2 file:text-sm file:font-semibold file:text-beige-surface">
                <p class="mt-1 text-xs text-espresso/60">JPG, PNG, or WEBP. Up to 10 images, 4MB each.</p>
                <x-input-error :messages="$errors->get('images')" class="mt-2" />
                <x-input-error :messages="$errors->get('images.*')" class="mt-2" />
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card space-y-5 p-6">
            <div>
                <x-input-label for="category_id" value="Category" />
                <select id="category_id" name="category_id" required class="input mt-1 w-full">
                    <option value="" disabled {{ old('category_id', $product->category_id ?? null) ? '' : 'selected' }}>Select a category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id ?? 0) === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="price" value="Price ({{ rufiyaa_sign() }} MVR)" />
                <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 w-full" value="{{ old('price', $product->price ?? '') }}" required />
                <x-input-error :messages="$errors->get('price')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="sale_price" value="Sale Price ({{ rufiyaa_sign() }} MVR, optional)" />
                <x-text-input id="sale_price" name="sale_price" type="number" step="0.01" min="0" class="mt-1 w-full" value="{{ old('sale_price', $product->sale_price ?? '') }}" />
                <x-input-error :messages="$errors->get('sale_price')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="stock_quantity" value="Stock Quantity" />
                <x-text-input id="stock_quantity" name="stock_quantity" type="number" min="0" class="mt-1 w-full" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required />
                <x-input-error :messages="$errors->get('stock_quantity')" class="mt-2" />
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_featured" value="1" class="rounded border-blush text-mocha focus:ring-sand"
                       {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}>
                <span class="text-sm font-semibold text-espresso">Featured on home page</span>
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded border-blush text-mocha focus:ring-sand"
                       {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm font-semibold text-espresso">Active (visible in shop)</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full">{{ isset($product) ? 'Update Product' : 'Create Product' }}</button>
    </div>
</div>

@isset($product)
    @if ($product->images->isNotEmpty())
        @push('scripts')
            <script>
                // Minimal, dependency-free HTML5 drag-to-reorder for the
                // product image grid — posts the new order to the
                // products.images.reorder endpoint on drop.
                document.addEventListener('DOMContentLoaded', () => {
                    const list = document.getElementById('image-sort-list');
                    if (! list) return;

                    let dragged = null;

                    list.querySelectorAll('[draggable="true"]').forEach((el) => {
                        el.addEventListener('dragstart', () => { dragged = el; el.classList.add('opacity-50'); });
                        el.addEventListener('dragend', () => { el.classList.remove('opacity-50'); });
                        el.addEventListener('dragover', (e) => e.preventDefault());
                        el.addEventListener('drop', (e) => {
                            e.preventDefault();
                            if (! dragged || dragged === el) return;
                            const items = Array.from(list.children);
                            const draggedIndex = items.indexOf(dragged);
                            const targetIndex = items.indexOf(el);
                            if (draggedIndex < targetIndex) {
                                el.after(dragged);
                            } else {
                                el.before(dragged);
                            }
                            persistOrder();
                        });
                    });

                    function persistOrder() {
                        const order = Array.from(list.children).map((el) => el.dataset.imageId);
                        fetch(list.dataset.reorderUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ order }),
                        });
                    }
                });
            </script>
        @endpush
    @endif
@endisset
