@csrf
@isset($customCharge) @method('PUT') @endisset

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="card space-y-5 p-6">
            <div>
                <x-input-label for="name" value="Charge Name" />
                <x-text-input id="name" name="name" class="mt-1 w-full" value="{{ old('name', $customCharge->name ?? '') }}" placeholder="e.g. Rush Order Fee, Service Charge" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="amount" value="Amount ({{ rufiyaa_sign() }} MVR)" />
                <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="mt-1 w-full" value="{{ old('amount', $customCharge->amount ?? '') }}" required />
                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card space-y-5 p-6">
            <div>
                <x-input-label for="sort_order" value="Sort Order" />
                <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 w-full" value="{{ old('sort_order', $customCharge->sort_order ?? 0) }}" />
            </div>

            <label class="flex items-start gap-2">
                <input type="checkbox" name="is_automatic" value="1" class="mt-0.5 rounded border-blush text-mocha focus:ring-sand"
                       {{ old('is_automatic', $customCharge->is_automatic ?? false) ? 'checked' : '' }}>
                <span class="text-sm">
                    <span class="font-semibold text-espresso">Apply automatically</span>
                    <span class="block text-xs text-espresso/60">Added to every order's total while active. Leave unchecked to only add it to specific orders yourself from the order's detail page.</span>
                </span>
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded border-blush text-mocha focus:ring-sand"
                       {{ old('is_active', $customCharge->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm font-semibold text-espresso">Active</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full">{{ isset($customCharge) ? 'Update Charge' : 'Create Charge' }}</button>
    </div>
</div>
