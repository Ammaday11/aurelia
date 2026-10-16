<x-app-layout title="Ribbons">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <p class="text-sm text-espresso/70">{{ $ribbons->count() }} {{ Str::plural('ribbon', $ribbons->count()) }}</p>
            <p class="text-xs text-espresso/50">What customers can pick for their ribbon on the <a href="{{ route('custom-bouquet') }}" target="_blank" class="text-mocha hover:underline">Custom Bouquet</a> builder.</p>
        </div>
        <a href="{{ route('admin.ribbons.create') }}" class="btn-primary">+ New Ribbon</a>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-blush text-xs uppercase tracking-wide text-mocha">
                    <th class="px-5 py-3">Ribbon</th>
                    <th class="px-5 py-3">Price</th>
                    <th class="px-5 py-3">Stock</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-blush">
                @forelse ($ribbons as $ribbon)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-lg bg-blush">
                                    @if ($ribbon->image)
                                        <img src="{{ $ribbon->imageUrl() }}" alt="{{ $ribbon->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-mocha/50"><x-icon name="ribbon" class="h-5 w-5" /></div>
                                    @endif
                                </div>
                                <span class="font-semibold text-espresso">{{ $ribbon->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-espresso/80"><x-price :amount="$ribbon->price" /></td>
                        <td class="px-5 py-3 text-espresso/80">{{ $ribbon->stock_quantity }}</td>
                        <td class="px-5 py-3">
                            @if (! $ribbon->is_active)
                                <span class="badge bg-blush text-espresso/60">Hidden</span>
                            @elseif ($ribbon->stock_quantity <= 0)
                                <span class="badge bg-red-100 text-red-700">Out of Stock</span>
                            @elseif ($ribbon->is_low_stock)
                                <span class="badge bg-mocha/20 text-mocha">Low Stock</span>
                            @else
                                <span class="badge bg-sand/30 text-espresso">Active</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.ribbons.edit', $ribbon) }}" class="text-mocha hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.ribbons.destroy', $ribbon) }}"
                                      onsubmit="return confirm('Delete this ribbon? This cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-espresso/60">No ribbons yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-app-layout>
