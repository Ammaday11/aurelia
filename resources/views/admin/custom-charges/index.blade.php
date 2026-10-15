<x-app-layout title="Custom Charges">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <p class="text-sm text-espresso/70">{{ $customCharges->count() }} {{ Str::plural('charge', $customCharges->count()) }}</p>
            <p class="text-xs text-espresso/50">An <strong>automatic</strong> active charge is added to every order's total. A <strong>manual</strong> one only applies when an admin attaches it from an order's detail page.</p>
        </div>
        <a href="{{ route('admin.custom-charges.create') }}" class="btn-primary">+ New Charge</a>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-blush text-xs uppercase tracking-wide text-mocha">
                    <th class="px-5 py-3">Charge</th>
                    <th class="px-5 py-3">Amount</th>
                    <th class="px-5 py-3">Applies</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-blush">
                @forelse ($customCharges as $charge)
                    <tr>
                        <td class="px-5 py-3 font-semibold text-espresso">{{ $charge->name }}</td>
                        <td class="px-5 py-3 text-espresso/80"><x-price :amount="$charge->amount" /></td>
                        <td class="px-5 py-3">
                            @if ($charge->is_automatic)
                                <span class="badge bg-sand/30 text-espresso">Automatic — every order</span>
                            @else
                                <span class="badge bg-blush text-espresso/70">Manual — admin adds per order</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if ($charge->is_active)
                                <span class="badge bg-sand/30 text-espresso">Active</span>
                            @else
                                <span class="badge bg-blush text-espresso/60">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.custom-charges.edit', $charge) }}" class="text-mocha hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.custom-charges.destroy', $charge) }}"
                                      onsubmit="return confirm('Delete this charge? This cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-espresso/60">No custom charges yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-app-layout>
