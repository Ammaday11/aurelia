@csrf
@isset($flower) @method('PUT') @endisset

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="card space-y-5 p-6">
            <div>
                <x-input-label for="name" value="Flower Name" />
                <x-text-input id="name" name="name" class="mt-1 w-full" value="{{ old('name', $flower->name ?? '') }}" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="price_per_stem" value="Price per Stem ({{ rufiyaa_sign() }} MVR)" />
                <x-text-input id="price_per_stem" name="price_per_stem" type="number" step="0.01" min="0" class="mt-1 w-full" value="{{ old('price_per_stem', $flower->price_per_stem ?? '') }}" required />
                <x-input-error :messages="$errors->get('price_per_stem')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="stock_quantity" value="Stock Quantity (stems)" />
                <x-text-input id="stock_quantity" name="stock_quantity" type="number" min="0" class="mt-1 w-full" value="{{ old('stock_quantity', $flower->stock_quantity ?? 0) }}" required />
                <x-input-error :messages="$errors->get('stock_quantity')" class="mt-2" />
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card space-y-5 p-6">
            <div>
                <x-input-label for="image" value="Flower Photo" />
                @isset($flower)
                    @if ($flower->image)
                        <img src="{{ $flower->imageUrl() }}" alt="{{ $flower->name }}" class="mt-2 h-32 w-full rounded-lg object-cover">
                    @endif
                @endisset
                <input type="file" id="image" name="image" accept="image/*"
                       class="input mt-2 w-full file:mr-4 file:rounded-full file:border-0 file:bg-mocha file:px-4 file:py-2 file:text-sm file:font-semibold file:text-beige-surface">
                <x-input-error :messages="$errors->get('image')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="sort_order" value="Sort Order" />
                <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 w-full" value="{{ old('sort_order', $flower->sort_order ?? 0) }}" />
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded border-blush text-mocha focus:ring-sand"
                       {{ old('is_active', $flower->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm font-semibold text-espresso">Active (selectable on the Custom Bouquet builder)</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full">{{ isset($flower) ? 'Update Flower' : 'Create Flower' }}</button>
    </div>
</div>
