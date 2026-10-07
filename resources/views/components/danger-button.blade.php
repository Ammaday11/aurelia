<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-full bg-red-600 px-6 py-3 font-sans text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-beige disabled:opacity-50']) }}>
    {{ $slot }}
</button>
