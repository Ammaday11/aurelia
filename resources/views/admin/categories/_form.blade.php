@csrf
@isset($category) @method('PUT') @endisset

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="card space-y-5 p-6">
            <div>
                <x-input-label for="name" value="Category Name" />
                <x-text-input id="name" name="name" class="mt-1 w-full" value="{{ old('name', $category->name ?? '') }}" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="slug" value="Slug (optional — auto-generated if left blank)" />
                <x-text-input id="slug" name="slug" class="mt-1 w-full" value="{{ old('slug', $category->slug ?? '') }}" />
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="Description" />
                <textarea id="description" name="description" rows="4" class="input mt-1 w-full">{{ old('description', $category->description ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card space-y-5 p-6">
            <div>
                <x-input-label for="image" value="Category Image" />
                @isset($category)
                    @if ($category->image)
                        <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" class="mt-2 h-32 w-full rounded-lg object-cover">
                    @endif
                @endisset
                <input type="file" id="image" name="image" accept="image/*"
                       class="input mt-2 w-full file:mr-4 file:rounded-full file:border-0 file:bg-mocha file:px-4 file:py-2 file:text-sm file:font-semibold file:text-beige-surface">
                <x-input-error :messages="$errors->get('image')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="sort_order" value="Sort Order" />
                <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 w-full" value="{{ old('sort_order', $category->sort_order ?? 0) }}" />
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded border-blush text-mocha focus:ring-sand"
                       {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm font-semibold text-espresso">Active (visible in shop)</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full">{{ isset($category) ? 'Update Category' : 'Create Category' }}</button>
    </div>
</div>
