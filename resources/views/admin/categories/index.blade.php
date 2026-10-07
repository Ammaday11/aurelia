<x-app-layout title="Categories">

    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-espresso/70">{{ $categories->count() }} {{ Str::plural('category', $categories->count()) }}</p>
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">+ New Category</a>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-blush text-xs uppercase tracking-wide text-mocha">
                    <th class="px-5 py-3">Category</th>
                    <th class="px-5 py-3">Products</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-blush">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-lg bg-blush">
                                    @if ($category->image)
                                        <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-mocha/50"><x-icon name="tag" class="h-5 w-5" /></div>
                                    @endif
                                </div>
                                <span class="font-semibold text-espresso">{{ $category->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-espresso/80">{{ $category->products_count }}</td>
                        <td class="px-5 py-3">
                            @if ($category->is_active)
                                <span class="badge bg-sand/30 text-espresso">Active</span>
                            @else
                                <span class="badge bg-blush text-espresso/60">Hidden</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="text-mocha hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                      onsubmit="return confirm('Delete this category? This cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-espresso/60">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-app-layout>
