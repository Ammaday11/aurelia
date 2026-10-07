@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-lg bg-sand/20 border border-sand px-4 py-3 text-sm font-semibold text-espresso']) }}>
        {{ $status }}
    </div>
@endif
