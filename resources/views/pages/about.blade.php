<x-storefront-layout title="About Us" description="Learn about Aurelia Boutonnière — our story, our values, and the hands behind every arrangement.">

    <section class="bg-blush/40 py-16 sm:py-24">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <p class="font-script text-4xl text-mocha">Our Story</p>
            <h1 class="mt-3 font-serif text-4xl text-espresso sm:text-5xl">About Aurelia Boutonnière</h1>
            <p class="mx-auto mt-6 max-w-xl text-base leading-relaxed text-espresso/80">
                {{ setting('about_intro', 'Aurelia Boutonnière began with a simple belief: flowers should feel personal. From our studio we hand-tie every bouquet to order, pairing seasonal blooms with a little bit of care for every occasion — big or small.') }}
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
            <img src="{{ setting('about_craft_image_path') ? asset('storage/'.setting('about_craft_image_path')) : asset('images/about/rooted-in-craft.png') }}" alt="A freshly hand-tied bouquet resting on the work table, beside a ribbon spool and shears" class="aspect-[4/3] w-full rounded-xl2 object-cover shadow-card">
            <div>
                <h2 class="font-serif text-3xl text-espresso">Rooted in Craft</h2>
                <p class="mt-4 leading-relaxed text-espresso/80">
                    Every arrangement that leaves our studio is designed and tied by hand — never mass-produced. We
                    work closely with trusted growers to source the freshest stems available, then take the time to
                    arrange each piece with intention.
                </p>
                <p class="mt-4 leading-relaxed text-espresso/80">
                    Whether it's a birthday, an anniversary, or simply a Tuesday that deserves flowers, our goal is
                    the same: a bouquet that feels like it was made just for the person receiving it.
                </p>
            </div>
        </div>

        <div class="mt-20 grid grid-cols-1 gap-12 lg:grid-cols-2">
            <div class="order-2 lg:order-1">
                <h2 class="font-serif text-3xl text-espresso">Delivered with Care</h2>
                <p class="mt-4 leading-relaxed text-espresso/80">
                    We know a bouquet is often a stand-in for a hug, a thank you, or an "I'm thinking of you" — so
                    getting it there safely and on time matters to us as much as the arrangement itself.
                </p>
                <p class="mt-4 leading-relaxed text-espresso/80">
                    Choose a delivery window that suits you at checkout, add a gift message, and we'll take care of
                    the rest across Malé and beyond.
                </p>
            </div>
            <img src="{{ setting('about_delivery_image_path') ? asset('storage/'.setting('about_delivery_image_path')) : asset('images/about/delivered-with-care.png') }}" alt="A wrapped bouquet carried in the front basket of a delivery scooter" class="order-1 aspect-[4/3] w-full rounded-xl2 object-cover shadow-card lg:order-2">
        </div>
    </section>

    <section class="bg-beige-surface py-16">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <p class="section-tagline">Our values</p>
            <h2 class="section-title mt-2">What We Stand For</h2>
            <div class="floral-divider my-6"></div>

            <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-3">
                @foreach ([
                    ['title' => 'Freshness First', 'body' => 'Flowers sourced and arranged the same day, never left sitting in storage.'],
                    ['title' => 'Honest Pricing', 'body' => 'Clear, upfront prices in Rufiyaa — no hidden fees at checkout.'],
                    ['title' => 'Personal Service', 'body' => 'Every order is a conversation, not a transaction. We\'re here to help.'],
                ] as $value)
                    <div>
                        <h3 class="font-serif text-xl text-espresso">{{ $value['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-espresso/70">{{ $value['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-16 text-center sm:px-6 lg:px-8">
        <h2 class="font-serif text-3xl text-espresso">Ready to send something beautiful?</h2>
        <div class="mt-8">
            <a href="{{ route('shop.index') }}" class="btn-primary">Shop Our Bouquets</a>
        </div>
    </section>

</x-storefront-layout>
