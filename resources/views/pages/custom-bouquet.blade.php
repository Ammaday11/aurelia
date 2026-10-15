<x-storefront-layout title="Custom Bouquet" description="Design your own bouquet — pick the flowers and how many of each, and we'll hand-tie it just for you.">

    <section class="bg-blush/30 py-12">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <p class="font-script text-3xl text-mocha">Design Your Own</p>
            <h1 class="mt-2 font-serif text-4xl text-espresso">Custom Bouquet</h1>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-espresso/80">
                Pick the flowers and how many of each — we'll hand-tie them into a bouquet made just for you.
            </p>
        </div>
    </section>

    @if ($flowers->isEmpty())
        <section class="mx-auto max-w-3xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <p class="text-espresso/70">We're not taking custom orders just this moment — check back soon, or browse our ready-made bouquets.</p>
            <a href="{{ route('shop.index') }}" class="btn-primary mt-6 inline-flex">Shop Our Bouquets</a>
        </section>
    @else
        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8"
                  x-data="customBouquet({
                      flowers: {{ $flowers->mapWithKeys(fn ($f) => [$f->id => ['name' => $f->name, 'price' => (float) $f->price_per_stem]])->toJson() }},
                      wrappingPapers: {{ $wrappingPapers->mapWithKeys(fn ($w) => [$w->id => ['name' => $w->name, 'price' => (float) $w->price]])->toJson() }},
                      ribbons: {{ $ribbons->mapWithKeys(fn ($r) => [$r->id => ['name' => $r->name, 'price' => (float) $r->price]])->toJson() }},
                      initialQty: {{ collect(old('flowers', []))->mapWithKeys(fn ($qty, $id) => [(int) $id => (int) $qty])->toJson() }},
                      initialWrap: {{ \Illuminate\Support\Js::from(old('wrapping_paper_id') ? (int) old('wrapping_paper_id') : null) }},
                      initialRibbon: {{ \Illuminate\Support\Js::from(old('ribbon_id') ? (int) old('ribbon_id') : null) }},
                      sign: {{ \Illuminate\Support\Js::from(rufiyaa_sign()) }},
                  })">
            <form method="POST" action="{{ route('cart.add-custom') }}">
                @csrf
                <input type="hidden" name="wrapping_paper_id" :value="wrap ?? ''">
                <input type="hidden" name="ribbon_id" :value="ribbon ?? ''">

                <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
                    <div class="lg:col-span-2 space-y-10">
                        <div>
                            <h2 class="font-serif text-xl text-espresso">Flowers</h2>
                            <x-input-error :messages="$errors->get('flowers')" class="mb-4 mt-1" />

                            <div class="grid grid-cols-2 gap-5 sm:grid-cols-3">
                                @foreach ($flowers as $flower)
                                    <div class="card flex flex-col gap-3 p-4">
                                        <div class="aspect-square overflow-hidden rounded-lg bg-blush">
                                            @if ($flower->image)
                                                <img src="{{ $flower->imageUrl() }}" alt="{{ $flower->name }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-mocha/50">
                                                    <x-icon name="flower" class="h-10 w-10" />
                                                </div>
                                            @endif
                                        </div>

                                        <div>
                                            <p class="font-serif text-base text-espresso">{{ $flower->name }}</p>
                                            <p class="text-xs text-mocha"><x-price :amount="$flower->price_per_stem" /> / stem</p>
                                        </div>

                                        <div class="mt-auto flex items-center justify-center gap-3">
                                            <button type="button"
                                                    @click="decrement({{ $flower->id }})"
                                                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border border-blush text-espresso hover:bg-blush"
                                                    aria-label="Fewer {{ $flower->name }}">&minus;</button>
                                            <input type="number" name="flowers[{{ $flower->id }}]" min="0" max="50"
                                                   x-model.number="qty[{{ $flower->id }}]"
                                                   class="input w-14 py-1 text-center">
                                            <button type="button"
                                                    @click="increment({{ $flower->id }})"
                                                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border border-blush text-espresso hover:bg-blush"
                                                    aria-label="More {{ $flower->name }}">+</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if ($wrappingPapers->isNotEmpty())
                            <div>
                                <h2 class="font-serif text-xl text-espresso">Wrapping Paper <span class="text-sm font-sans font-normal text-espresso/60">(optional)</span></h2>
                                <x-input-error :messages="$errors->get('wrapping_paper_id')" class="mb-4 mt-1" />

                                <div class="mt-4 grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-5">
                                    <button type="button" @click="wrap = null"
                                            class="card flex flex-col items-center gap-2 p-3 text-center"
                                            :class="wrap === null ? 'ring-2 ring-mocha' : ''">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-blush text-mocha/50">
                                            <x-icon name="close" class="h-5 w-5" />
                                        </div>
                                        <span class="text-xs font-semibold text-espresso">None</span>
                                    </button>
                                    @foreach ($wrappingPapers as $paper)
                                        <button type="button" @click="wrap = {{ $paper->id }}"
                                                class="card flex flex-col items-center gap-2 p-3 text-center"
                                                :class="wrap === {{ $paper->id }} ? 'ring-2 ring-mocha' : ''">
                                            <div class="h-14 w-14 overflow-hidden rounded-lg bg-blush">
                                                @if ($paper->image)
                                                    <img src="{{ $paper->imageUrl() }}" alt="{{ $paper->name }}" class="h-full w-full object-cover">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center text-mocha/50"><x-icon name="gift" class="h-5 w-5" /></div>
                                                @endif
                                            </div>
                                            <span class="text-xs font-semibold text-espresso">{{ $paper->name }}</span>
                                            <span class="text-xs text-mocha"><x-price :amount="$paper->price" /></span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($ribbons->isNotEmpty())
                            <div>
                                <h2 class="font-serif text-xl text-espresso">Ribbon <span class="text-sm font-sans font-normal text-espresso/60">(optional)</span></h2>
                                <x-input-error :messages="$errors->get('ribbon_id')" class="mb-4 mt-1" />

                                <div class="mt-4 grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-5">
                                    <button type="button" @click="ribbon = null"
                                            class="card flex flex-col items-center gap-2 p-3 text-center"
                                            :class="ribbon === null ? 'ring-2 ring-mocha' : ''">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-blush text-mocha/50">
                                            <x-icon name="close" class="h-5 w-5" />
                                        </div>
                                        <span class="text-xs font-semibold text-espresso">None</span>
                                    </button>
                                    @foreach ($ribbons as $r)
                                        <button type="button" @click="ribbon = {{ $r->id }}"
                                                class="card flex flex-col items-center gap-2 p-3 text-center"
                                                :class="ribbon === {{ $r->id }} ? 'ring-2 ring-mocha' : ''">
                                            <div class="h-14 w-14 overflow-hidden rounded-lg bg-blush">
                                                @if ($r->image)
                                                    <img src="{{ $r->imageUrl() }}" alt="{{ $r->name }}" class="h-full w-full object-cover">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center text-mocha/50"><x-icon name="ribbon" class="h-5 w-5" /></div>
                                                @endif
                                            </div>
                                            <span class="text-xs font-semibold text-espresso">{{ $r->name }}</span>
                                            <span class="text-xs text-mocha"><x-price :amount="$r->price" /></span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="lg:col-span-1">
                        <div class="card sticky top-24 space-y-5 p-6">
                            <h2 class="font-serif text-xl text-espresso">Your Bouquet</h2>

                            <p x-show="lines.length === 0" class="text-sm text-espresso/60">
                                Choose flowers to start building your bouquet.
                            </p>

                            <ul x-show="lines.length > 0 || wrap || ribbon" class="space-y-2">
                                <template x-for="line in lines" :key="line.id">
                                    <li class="flex items-center justify-between gap-3 text-sm">
                                        <span class="text-espresso" x-text="line.qty + '× ' + line.name"></span>
                                        <span class="price-rufiyaa flex-shrink-0 text-espresso" x-text="formatPrice(line.total)"></span>
                                    </li>
                                </template>
                                <li x-show="wrap" class="flex items-center justify-between gap-3 text-sm">
                                    <span class="text-espresso" x-text="wrap ? wrappingPapers[wrap].name + ' wrap' : ''"></span>
                                    <span class="price-rufiyaa flex-shrink-0 text-espresso" x-text="wrap ? formatPrice(wrappingPapers[wrap].price) : ''"></span>
                                </li>
                                <li x-show="ribbon" class="flex items-center justify-between gap-3 text-sm">
                                    <span class="text-espresso" x-text="ribbon ? ribbons[ribbon].name + ' ribbon' : ''"></span>
                                    <span class="price-rufiyaa flex-shrink-0 text-espresso" x-text="ribbon ? formatPrice(ribbons[ribbon].price) : ''"></span>
                                </li>
                            </ul>

                            <div class="flex items-center justify-between border-t border-blush pt-4 text-sm font-semibold text-espresso">
                                <span>Subtotal (per bouquet)</span>
                                <span class="price-rufiyaa" x-text="formatPrice(subtotal)"></span>
                            </div>

                            <div>
                                <label for="quantity" class="label">Number of this bouquet</label>
                                <x-text-input id="quantity" name="quantity" type="number" min="1" max="20" value="{{ old('quantity', 1) }}" class="w-24" />
                                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                            </div>

                            <div>
                                <label for="gift_message" class="label">Gift Message (optional)</label>
                                <textarea id="gift_message" name="gift_message" rows="3" maxlength="500" class="input w-full">{{ old('gift_message') }}</textarea>
                                <x-input-error :messages="$errors->get('gift_message')" class="mt-2" />
                            </div>

                            <button type="submit" class="btn-primary w-full" :disabled="stemCount === 0" :class="{ 'opacity-50 cursor-not-allowed': stemCount === 0 }">
                                Add Custom Bouquet to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    @endif

    <script>
        // Live per-bouquet total as the customer adjusts stem counts —
        // purely a display convenience; the server (CartService::addCustom(),
        // OrderService::placeOrder()) always recomputes prices from the
        // current Flower rows and never trusts anything posted from here.
        // Defined as a plain (non-module) inline script, so it runs and
        // registers globally before Alpine's deferred module script scans
        // the DOM for the x-data="customBouquet(...)" expression above.
        function customBouquet({ flowers, wrappingPapers, ribbons, initialQty, initialWrap, initialRibbon, sign }) {
            return {
                flowers,
                wrappingPapers,
                ribbons,
                qty: Object.fromEntries(Object.keys(flowers).map((id) => [id, initialQty[id] || 0])),
                wrap: initialWrap,
                ribbon: initialRibbon,

                get lines() {
                    return Object.entries(this.qty)
                        .filter(([, q]) => (q || 0) > 0)
                        .map(([id, q]) => ({
                            id,
                            name: this.flowers[id].name,
                            qty: q,
                            total: this.flowers[id].price * q,
                        }));
                },

                get subtotal() {
                    const flowersTotal = this.lines.reduce((sum, line) => sum + line.total, 0);
                    const wrapPrice = this.wrap ? this.wrappingPapers[this.wrap].price : 0;
                    const ribbonPrice = this.ribbon ? this.ribbons[this.ribbon].price : 0;

                    return flowersTotal + wrapPrice + ribbonPrice;
                },

                get stemCount() {
                    return this.lines.reduce((sum, line) => sum + line.qty, 0);
                },

                increment(id) {
                    this.qty[id] = Math.min(50, (this.qty[id] || 0) + 1);
                },

                decrement(id) {
                    this.qty[id] = Math.max(0, (this.qty[id] || 0) - 1);
                },

                formatPrice(amount) {
                    return sign + ' ' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
            };
        }
    </script>

</x-storefront-layout>
