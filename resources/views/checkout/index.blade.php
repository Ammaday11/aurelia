<x-storefront-layout title="Checkout" description="Complete your order — no account needed.">

    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-10 text-center">
            <p class="font-script text-3xl text-mocha">Final step</p>
            <h1 class="mt-2 font-serif text-4xl text-espresso">Checkout</h1>
            <p class="mt-2 text-sm text-espresso/70">No account required — just fill in your delivery details below.</p>
        </div>

        <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data"
              x-data="{ paymentMethod: '{{ old('payment_method', 'cod') }}' }"
              class="grid grid-cols-1 gap-10 lg:grid-cols-5">
            @csrf

            <div class="space-y-8 lg:col-span-3">

                {{-- Contact & delivery --}}
                <div class="card space-y-5 p-6 sm:p-8">
                    <h2 class="font-serif text-xl text-espresso">Delivery Details</h2>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="name" value="Full Name" />
                            <x-text-input id="name" name="name" class="mt-1 w-full" value="{{ old('name') }}" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="phone" value="Phone Number" />
                            <x-text-input id="phone" name="phone" class="mt-1 w-full" value="{{ old('phone') }}" required />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="email" value="Email Address" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 w-full" value="{{ old('email') }}" required />
                        <p class="mt-1 text-xs text-espresso/60">We'll send your order confirmation here.</p>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="delivery_address" value="Delivery Address" />
                        <textarea id="delivery_address" name="delivery_address" rows="3" required class="input mt-1 w-full">{{ old('delivery_address') }}</textarea>
                        <x-input-error :messages="$errors->get('delivery_address')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="delivery_date" value="Delivery Date" />
                            <x-text-input id="delivery_date" name="delivery_date" type="date" class="mt-1 w-full"
                                          min="{{ $minDeliveryDate }}" value="{{ old('delivery_date', $minDeliveryDate) }}" required />
                            <x-input-error :messages="$errors->get('delivery_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="delivery_time_slot" value="Delivery Time Slot" />
                            <select id="delivery_time_slot" name="delivery_time_slot" required class="input mt-1 w-full">
                                <option value="" disabled {{ old('delivery_time_slot') ? '' : 'selected' }}>Choose a time slot</option>
                                @foreach ($timeSlots as $slot)
                                    <option value="{{ $slot }}" @selected(old('delivery_time_slot') === $slot)>{{ $slot }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('delivery_time_slot')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="gift_message" value="Gift Message (optional)" />
                        <textarea id="gift_message" name="gift_message" rows="2" maxlength="500" class="input mt-1 w-full">{{ old('gift_message') }}</textarea>
                        <x-input-error :messages="$errors->get('gift_message')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="special_notes" value="Special Notes (optional)" />
                        <textarea id="special_notes" name="special_notes" rows="2" maxlength="1000"
                                  placeholder="e.g. landmark, gate code, preferred contact method..."
                                  class="input mt-1 w-full">{{ old('special_notes') }}</textarea>
                        <x-input-error :messages="$errors->get('special_notes')" class="mt-2" />
                    </div>
                </div>

                {{-- Payment --}}
                <div class="card space-y-5 p-6 sm:p-8">
                    <h2 class="font-serif text-xl text-espresso">Payment Method</h2>

                    <div class="space-y-3">
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-blush p-4 transition"
                               :class="paymentMethod === 'cod' ? 'border-sand bg-sand/10' : ''">
                            <input type="radio" name="payment_method" value="cod" x-model="paymentMethod" class="mt-1 text-mocha focus:ring-sand" required>
                            <span>
                                <span class="block font-semibold text-espresso">Cash on Delivery</span>
                                <span class="block text-sm text-espresso/70">Pay in cash when your order arrives.</span>
                            </span>
                        </label>

                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-blush p-4 transition"
                               :class="paymentMethod === 'bank_transfer' ? 'border-sand bg-sand/10' : ''">
                            <input type="radio" name="payment_method" value="bank_transfer" x-model="paymentMethod" class="mt-1 text-mocha focus:ring-sand">
                            <span>
                                <span class="block font-semibold text-espresso">Bank Transfer</span>
                                <span class="block text-sm text-espresso/70">Transfer the total and upload your receipt below.</span>
                            </span>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />

                    <div x-show="paymentMethod === 'bank_transfer'" x-cloak x-transition class="space-y-4 rounded-lg bg-blush/40 p-4">
                        <div class="text-sm text-espresso/85">
                            <p class="font-semibold text-espresso">Bank Transfer Details</p>
                            <dl class="mt-2 space-y-1">
                                @if ($bankDetails['bank_name'])
                                    <div class="flex justify-between"><dt>Bank</dt><dd>{{ $bankDetails['bank_name'] }}</dd></div>
                                @endif
                                @if ($bankDetails['account_name'])
                                    <div class="flex justify-between"><dt>Account Name</dt><dd>{{ $bankDetails['account_name'] }}</dd></div>
                                @endif
                                @if ($bankDetails['account_number'])
                                    <div class="flex justify-between"><dt>Account Number</dt><dd>{{ $bankDetails['account_number'] }}</dd></div>
                                @endif
                            </dl>
                        </div>

                        <div>
                            <x-input-label for="payment_receipt" value="Upload Receipt" />
                            <input type="file" id="payment_receipt" name="payment_receipt" accept="image/*"
                                   class="input mt-1 w-full file:mr-4 file:rounded-full file:border-0 file:bg-mocha file:px-4 file:py-2 file:text-sm file:font-semibold file:text-beige-surface">
                            <x-input-error :messages="$errors->get('payment_receipt')" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order summary --}}
            <div class="lg:col-span-2">
                <div class="card sticky top-24 space-y-5 p-6">
                    <h2 class="font-serif text-xl text-espresso">Order Summary</h2>

                    <ul class="max-h-64 space-y-3 overflow-y-auto pr-1">
                        @foreach ($items as $item)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-espresso">{{ $item->product->title }}</p>
                                    <p class="text-xs text-espresso/60">Qty {{ $item->quantity }}</p>
                                </div>
                                <x-price :amount="$item->line_total" class="flex-shrink-0 text-espresso" />
                            </li>
                        @endforeach
                    </ul>

                    <div class="space-y-2 border-t border-blush pt-4 text-sm">
                        <div class="flex justify-between text-espresso/80">
                            <span>Subtotal</span>
                            <x-price :amount="$subtotal" />
                        </div>
                        <div class="flex justify-between text-espresso/80">
                            <span>Delivery Fee</span>
                            <x-price :amount="$deliveryFee" />
                        </div>
                        <div class="flex justify-between border-t border-blush pt-2 text-base font-bold text-espresso">
                            <span>Total</span>
                            <x-price :amount="$subtotal + $deliveryFee" />
                        </div>
                    </div>

                    <button type="submit" class="btn-primary w-full">Place Order</button>
                    <p class="text-center text-xs text-espresso/60">By placing your order you agree to our delivery terms.</p>
                </div>
            </div>
        </form>
    </section>

</x-storefront-layout>
