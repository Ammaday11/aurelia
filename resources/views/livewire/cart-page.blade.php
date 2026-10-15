<div>
    @if ($items->isEmpty())
        <div class="card p-12 text-center">
            <x-icon name="cart" class="mx-auto h-12 w-12 text-mocha/50" />
            <p class="mt-4 font-serif text-xl text-espresso">Your cart is empty</p>
            <p class="mt-2 text-sm text-espresso/70">Looks like you haven't added any bouquets yet.</p>
            <a href="{{ route('shop.index') }}" class="btn-primary mt-6 inline-flex">Start Shopping</a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                @foreach ($items as $item)
                    <div wire:key="cart-row-{{ $item->row_id }}" class="card flex gap-4 p-4 sm:p-5">
                        @if ($item->is_custom)
                            <div class="flex h-24 w-24 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-blush text-mocha/50 sm:h-28 sm:w-28">
                                <x-icon name="sparkles" class="h-8 w-8" />
                            </div>
                        @else
                            <a href="{{ route('shop.show', $item->product) }}" class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg bg-blush sm:h-28 sm:w-28">
                                @if ($image = $item->product->images->first())
                                    <img src="{{ $image->thumbUrl() }}" alt="{{ $item->product->title }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-mocha/50">
                                        <x-icon name="flower" class="h-8 w-8" />
                                    </div>
                                @endif
                            </a>
                        @endif

                        <div class="flex min-w-0 flex-1 flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    @if ($item->is_custom)
                                        <span class="font-serif text-lg text-espresso">Custom Bouquet</span>
                                    @else
                                        <a href="{{ route('shop.show', $item->product) }}" class="font-serif text-lg text-espresso hover:text-mocha">
                                            {{ $item->product->title }}
                                        </a>
                                    @endif
                                    <button wire:click="removeItem('{{ $item->row_id }}')" class="flex-shrink-0 text-espresso/40 hover:text-red-600" aria-label="Remove item">
                                        <x-icon name="trash" class="h-5 w-5" />
                                    </button>
                                </div>

                                @if ($item->is_custom)
                                    <p class="text-xs text-espresso/70">
                                        {{ $item->flowers->map(fn ($line) => "{$line->quantity}\u{00D7} {$line->flower->name}")->implode(', ') }}
                                    </p>
                                    @if ($item->wrappingPaper || $item->ribbon)
                                        <p class="text-xs text-espresso/70">
                                            {{ collect([
                                                $item->wrappingPaper ? "Wrapped in {$item->wrappingPaper->name}" : null,
                                                $item->ribbon ? "{$item->ribbon->name} ribbon" : null,
                                            ])->filter()->implode(' · ') }}
                                        </p>
                                    @endif
                                @endif

                                <x-price :amount="$item->unit_price" class="text-sm text-mocha" />
                                @if ($item->gift_message)
                                    <p class="mt-1 text-xs italic text-espresso/60">Gift note: "{{ \Illuminate\Support\Str::limit($item->gift_message, 60) }}"</p>
                                @endif
                                @if ($item->exceeds_stock)
                                    <p class="mt-1 text-xs font-semibold text-red-600">Only {{ $item->product->stock_quantity }} left — quantity adjusted.</p>
                                @endif
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <input type="number" min="0" max="{{ $item->is_custom ? 20 : max($item->product->stock_quantity, 1) }}"
                                           wire:model="quantities.{{ $item->row_id }}"
                                           wire:change="updateQuantity('{{ $item->row_id }}')"
                                           class="input w-20 py-1.5 text-center">
                                </div>
                                <x-price :amount="$item->line_total" class="font-semibold text-espresso" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="lg:col-span-1">
                <div class="card sticky top-24 space-y-5 p-6">
                    <h2 class="font-serif text-xl text-espresso">Order Summary</h2>
                    <div class="flex items-center justify-between text-sm text-espresso/80">
                        <span>Subtotal</span>
                        <x-price :amount="$subtotal" class="font-semibold text-espresso" />
                    </div>
                    <p class="text-xs text-espresso/60">Delivery fee is calculated at checkout.</p>
                    <a href="{{ route('checkout.index') }}" class="btn-primary w-full">Proceed to Checkout</a>
                    <a href="{{ route('shop.index') }}" class="block text-center text-sm text-mocha hover:underline">Continue Shopping</a>
                </div>
            </div>
        </div>
    @endif
</div>
