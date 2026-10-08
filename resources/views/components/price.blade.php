@props([
    'amount',
    'class' => '',
    'withSign' => true,
    'for' => 'web',
])

{{--
    The single reusable price display used across the storefront, cart,
    checkout, admin panel, emails, and PDF invoices. Always formats
    through the money() helper so every price on the site is consistent.

    - for="web" (default): the literal Rufiyaa sign character stays in the
      text node (not an HTML entity) so copy/paste and screen readers get
      the real symbol, styled via the "price-rufiyaa" class which loads
      the bundled RufiyaaSign webfont scoped to that one code point (see
      resources/css/app.css). The character lives at a Private Use Area
      codepoint rather than a "real" currency codepoint — there isn't one
      yet; see resources/fonts/build_rufiyaa_font.py. aria-label spells
      out "MVR" for assistive tech, since there's no standard pronunciation
      for a PUA codepoint.
    - for="image": custom webfonts are unreliable in mail clients and
      print-to-PDF rendering, so emails and the printable invoice both use
      this mode to swap the character for an inline <img> of the sign
      instead (per the brief), with alt="MVR".
--}}
@if ($for === 'image')
    <span {{ $attributes }}><img src="{{ asset('images/mail/rufiyaa-sign.png') }}" alt="MVR" width="25" height="16" style="display:inline-block;vertical-align:-2px;height:16px;width:auto;border:0;"> {{ money($amount, false) }}</span>
@else
    <span {{ $attributes->class(['price-rufiyaa', $class]) }} aria-label="MVR {{ number_format((float) $amount, 2) }}">{{ money($amount, $withSign) }}</span>
@endif
