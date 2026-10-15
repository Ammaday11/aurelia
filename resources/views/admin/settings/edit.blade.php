<x-app-layout title="Settings">

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- Shop identity --}}
        <div class="card space-y-5 p-6 sm:p-8">
            <h2 class="font-serif text-xl text-espresso">Shop Identity</h2>

            <div>
                <x-input-label for="shop_name" value="Shop Name" />
                <x-text-input id="shop_name" name="shop_name" class="mt-1 w-full max-w-md" value="{{ old('shop_name', setting('shop_name', config('shop.name'))) }}" required />
                <x-input-error :messages="$errors->get('shop_name')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="logo" value="Logo (SVG or PNG)" />
                    @if (setting('logo_path'))
                        <div class="mt-2 flex h-16 items-center rounded-lg border border-blush bg-beige p-3"><x-logo :link="false" /></div>
                    @else
                        <p class="mt-2 text-xs text-espresso/60">No logo uploaded yet — using the text placeholder shown across the site.</p>
                    @endif
                    <input type="file" id="logo" name="logo" accept="image/png,image/svg+xml,image/jpeg,image/webp"
                           class="input mt-2 w-full file:mr-4 file:rounded-full file:border-0 file:bg-mocha file:px-4 file:py-2 file:text-sm file:font-semibold file:text-beige-surface">
                    <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="favicon" value="Favicon" />
                    @if (setting('favicon_path'))
                        <img src="{{ asset('storage/'.setting('favicon_path')) }}" alt="Favicon" class="mt-2 h-10 w-10 rounded">
                    @else
                        <p class="mt-2 text-xs text-espresso/60">No favicon uploaded — using the default "A" mark.</p>
                    @endif
                    <input type="file" id="favicon" name="favicon" accept="image/png,image/x-icon,image/svg+xml"
                           class="input mt-2 w-full file:mr-4 file:rounded-full file:border-0 file:bg-mocha file:px-4 file:py-2 file:text-sm file:font-semibold file:text-beige-surface">
                    <x-input-error :messages="$errors->get('favicon')" class="mt-2" />
                </div>
            </div>
        </div>

        {{-- Contact info --}}
        <div class="card space-y-5 p-6 sm:p-8">
            <h2 class="font-serif text-xl text-espresso">Contact Information</h2>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="contact_email" value="Contact Email" />
                    <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 w-full" value="{{ old('contact_email', setting('contact_email')) }}" />
                    <p class="mt-1 text-xs text-espresso/60">Contact form submissions are sent here.</p>
                </div>
                <div>
                    <x-input-label for="contact_phone" value="Contact Phone" />
                    <x-text-input id="contact_phone" name="contact_phone" class="mt-1 w-full" value="{{ old('contact_phone', setting('contact_phone')) }}" />
                </div>
            </div>

            <div>
                <x-input-label for="contact_address" value="Address" />
                <textarea id="contact_address" name="contact_address" rows="2" class="input mt-1 w-full">{{ old('contact_address', setting('contact_address')) }}</textarea>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <x-input-label for="opening_hours" value="Opening Hours" />
                    <x-text-input id="opening_hours" name="opening_hours" class="mt-1 w-full" value="{{ old('opening_hours', setting('opening_hours')) }}" />
                </div>
                <div>
                    <x-input-label for="social_facebook" value="Facebook URL" />
                    <x-text-input id="social_facebook" name="social_facebook" class="mt-1 w-full" value="{{ old('social_facebook', setting('social_facebook')) }}" />
                </div>
                <div>
                    <x-input-label for="social_instagram" value="Instagram URL" />
                    <x-text-input id="social_instagram" name="social_instagram" class="mt-1 w-full" value="{{ old('social_instagram', setting('social_instagram')) }}" />
                </div>
                <div>
                    <x-input-label for="social_tiktok" value="TikTok URL" />
                    <x-text-input id="social_tiktok" name="social_tiktok" class="mt-1 w-full" value="{{ old('social_tiktok', setting('social_tiktok')) }}" />
                </div>
            </div>
        </div>

        {{-- Delivery --}}
        <div class="card space-y-5 p-6 sm:p-8" x-data="{ slots: {{ json_encode(old('delivery_time_slots', $timeSlots)) }} }">
            <h2 class="font-serif text-xl text-espresso">Delivery</h2>

            <div>
                <x-input-label for="delivery_fee" value="Delivery Fee ({{ rufiyaa_sign() }} MVR)" />
                <x-text-input id="delivery_fee" name="delivery_fee" type="number" step="0.01" min="0" class="mt-1 w-full max-w-xs"
                              value="{{ old('delivery_fee', setting('delivery_fee', config('shop.default_delivery_fee'))) }}" required />
                <x-input-error :messages="$errors->get('delivery_fee')" class="mt-2" />
            </div>

            <div>
                <x-input-label value="Delivery Time Slots" />
                <div class="mt-2 space-y-2">
                    <template x-for="(slot, index) in slots" :key="index">
                        <div class="flex gap-2">
                            <input type="text" :name="'delivery_time_slots[' + index + ']'" x-model="slots[index]" class="input flex-1">
                            <button type="button" @click="slots.splice(index, 1)" class="btn-outline px-3" aria-label="Remove slot">
                                <x-icon name="close" class="h-4 w-4" />
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="slots.push('')" class="btn-outline mt-3">+ Add Time Slot</button>
                <x-input-error :messages="$errors->get('delivery_time_slots')" class="mt-2" />
            </div>
        </div>

        {{-- Bank details --}}
        <div class="card space-y-5 p-6 sm:p-8">
            <h2 class="font-serif text-xl text-espresso">Bank Transfer Details</h2>
            <p class="text-sm text-espresso/60">Shown to customers at checkout when they choose Bank Transfer.</p>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <x-input-label for="bank_name" value="Bank Name" />
                    <x-text-input id="bank_name" name="bank_name" class="mt-1 w-full" value="{{ old('bank_name', setting('bank_name')) }}" />
                </div>
                <div>
                    <x-input-label for="bank_account_name" value="Account Name" />
                    <x-text-input id="bank_account_name" name="bank_account_name" class="mt-1 w-full" value="{{ old('bank_account_name', setting('bank_account_name')) }}" />
                </div>
                <div>
                    <x-input-label for="bank_account_number" value="Account Number" />
                    <x-text-input id="bank_account_number" name="bank_account_number" class="mt-1 w-full" value="{{ old('bank_account_number', setting('bank_account_number')) }}" />
                </div>
            </div>
        </div>

        {{-- Site content --}}
        <div class="card space-y-5 p-6 sm:p-8">
            <h2 class="font-serif text-xl text-espresso">Site Content</h2>

            <div>
                <x-input-label for="hero_tagline" value="Home Page Hero Text" />
                <textarea id="hero_tagline" name="hero_tagline" rows="2" class="input mt-1 w-full">{{ old('hero_tagline', setting('hero_tagline')) }}</textarea>
            </div>
            <div>
                <x-input-label for="about_intro" value="About Page Intro" />
                <textarea id="about_intro" name="about_intro" rows="3" class="input mt-1 w-full">{{ old('about_intro', setting('about_intro')) }}</textarea>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="about_craft_image" value='About Page — "Rooted in Craft" Image' />
                    <img src="{{ asset(setting('about_craft_image_path') ? 'storage/'.setting('about_craft_image_path') : 'images/about/rooted-in-craft.png') }}" alt="" class="mt-2 aspect-[4/3] w-full max-w-xs rounded-lg border border-blush object-cover">
                    <p class="mt-1 text-xs text-espresso/60">{{ setting('about_craft_image_path') ? 'Your uploaded photo.' : 'Using the bundled default illustration — upload a photo to replace it.' }}</p>
                    <input type="file" id="about_craft_image" name="about_craft_image" accept="image/png,image/jpeg,image/webp"
                           class="input mt-2 w-full file:mr-4 file:rounded-full file:border-0 file:bg-mocha file:px-4 file:py-2 file:text-sm file:font-semibold file:text-beige-surface">
                    <x-input-error :messages="$errors->get('about_craft_image')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="about_delivery_image" value='About Page — "Delivered with Care" Image' />
                    <img src="{{ asset(setting('about_delivery_image_path') ? 'storage/'.setting('about_delivery_image_path') : 'images/about/delivered-with-care.png') }}" alt="" class="mt-2 aspect-[4/3] w-full max-w-xs rounded-lg border border-blush object-cover">
                    <p class="mt-1 text-xs text-espresso/60">{{ setting('about_delivery_image_path') ? 'Your uploaded photo.' : 'Using the bundled default illustration — upload a photo to replace it.' }}</p>
                    <input type="file" id="about_delivery_image" name="about_delivery_image" accept="image/png,image/jpeg,image/webp"
                           class="input mt-2 w-full file:mr-4 file:rounded-full file:border-0 file:bg-mocha file:px-4 file:py-2 file:text-sm file:font-semibold file:text-beige-surface">
                    <x-input-error :messages="$errors->get('about_delivery_image')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="footer_tagline" value="Footer Tagline" />
                <textarea id="footer_tagline" name="footer_tagline" rows="2" class="input mt-1 w-full">{{ old('footer_tagline', setting('footer_tagline')) }}</textarea>
            </div>
            <div>
                <x-input-label for="meta_description" value="Default SEO Meta Description" />
                <textarea id="meta_description" name="meta_description" rows="2" maxlength="300" class="input mt-1 w-full">{{ old('meta_description', setting('meta_description')) }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn-primary">Save Settings</button>
    </form>

</x-app-layout>
