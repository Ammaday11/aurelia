@props(['label', 'icon' => 'box'])

{{-- Note: intentionally no dynamic color prop — Tailwind's JIT scanner
     needs full literal class names in the source, so this always uses the
     sand/mocha accent rather than interpolating a class name at runtime. --}}
<div class="card p-6">
    <div class="flex items-center justify-between">
        <p class="text-sm font-semibold uppercase tracking-wide text-mocha">{{ $label }}</p>
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-sand/20 text-mocha">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
    </div>
    <div class="mt-3 font-serif text-3xl text-espresso">
        {{ $slot }}
    </div>
</div>
