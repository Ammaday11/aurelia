<x-storefront-layout title="Contact Us" description="Get in touch with Aurelia Boutonnière — questions, custom orders, or anything else, we'd love to hear from you.">

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="font-script text-4xl text-mocha">Say Hello</p>
            <h1 class="mt-2 font-serif text-4xl text-espresso sm:text-5xl">Contact Us</h1>
            <p class="mx-auto mt-4 max-w-xl text-espresso/80">
                Questions about an order, a custom arrangement, or just want to say hi? Send us a message and
                we'll get back to you as soon as we can.
            </p>
        </div>

        <div class="mt-14 grid grid-cols-1 gap-12 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <form method="POST" action="{{ route('pages.contact.submit') }}" class="card space-y-5 p-6 sm:p-8">
                    @csrf

                    {{-- Honeypot — hidden from real visitors via CSS, bots that
                         auto-fill every field get silently rejected server-side. --}}
                    <div class="absolute -left-[9999px]" aria-hidden="true">
                        <label for="website">Leave this field empty</label>
                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="name" value="Your Name" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 w-full" value="{{ old('name') }}" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email Address" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 w-full" value="{{ old('email') }}" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="phone" value="Phone (optional)" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 w-full" value="{{ old('phone') }}" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="subject" value="Subject" />
                            <x-text-input id="subject" name="subject" type="text" class="mt-1 w-full" value="{{ old('subject') }}" />
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="message" value="Message" />
                        <textarea id="message" name="message" rows="5" class="input mt-1 w-full" required>{{ old('message') }}</textarea>
                        <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    </div>

                    <x-primary-button>Send Message</x-primary-button>
                </form>
            </div>

            <div class="lg:col-span-2">
                <div class="card space-y-6 p-6 sm:p-8">
                    <div>
                        <h2 class="font-serif text-xl text-espresso">Visit or Reach Us</h2>
                        <div class="mt-4 space-y-3 text-sm text-espresso/80">
                            @if ($address = setting('contact_address'))
                                <p class="flex gap-3"><x-icon name="box" class="h-5 w-5 flex-shrink-0 text-mocha" /> {{ $address }}</p>
                            @endif
                            @if ($phone = setting('contact_phone'))
                                <p class="flex gap-3"><x-icon name="mail" class="h-5 w-5 flex-shrink-0 text-mocha" /> <a href="tel:{{ $phone }}" class="hover:text-mocha">{{ $phone }}</a></p>
                            @endif
                            @if ($email = setting('contact_email'))
                                <p class="flex gap-3"><x-icon name="mail" class="h-5 w-5 flex-shrink-0 text-mocha" /> <a href="mailto:{{ $email }}" class="hover:text-mocha">{{ $email }}</a></p>
                            @endif
                        </div>
                    </div>

                    <div class="floral-divider"></div>

                    <div>
                        <h2 class="font-serif text-xl text-espresso">Opening Hours</h2>
                        <p class="mt-3 text-sm text-espresso/80">{{ setting('opening_hours', 'Daily, 9:00am – 9:00pm') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-storefront-layout>
