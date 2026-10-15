@props(['name'])

{{-- Minimal hand-drawn line icons (no icon package dependency) for the admin sidebar. --}}
@switch($name)
    @case('home')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5 9.5V21h5v-6h4v6h5V9.5" />
        </svg>
        @break

    @case('flower')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <circle cx="12" cy="12" r="2.25" />
            <path stroke-linecap="round" d="M12 9.75c0-2.5-1.5-4-2.5-5.25C8.5 6 7 7.5 7 9.75S8.5 12 9.75 12M12 9.75c0-2.5 1.5-4 2.5-5.25C15.5 6 17 7.5 17 9.75S15.5 12 14.25 12M12 14.25c0 2.5-1.5 4-2.5 5.25C8.5 18 7 16.5 7 14.25S8.5 12 9.75 12M12 14.25c0 2.5 1.5 4 2.5 5.25C15.5 18 17 16.5 17 14.25S15.5 12 14.25 12" />
        </svg>
        @break

    @case('tag')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 11 8-8h7a1 1 0 0 1 1 1v7l-8 8a1.5 1.5 0 0 1-2 0l-6-6a1.5 1.5 0 0 1 0-2Z" />
            <circle cx="15.5" cy="8.5" r="1.25" />
        </svg>
        @break

    @case('box')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 8 12 3.5 20.5 8M3.5 8v8L12 20.5M3.5 8 12 12.5m0 8V12.5m0 0L20.5 8m0 0v8L12 20.5" />
        </svg>
        @break

    @case('mail')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <rect x="3" y="5.5" width="18" height="13" rx="2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8 6 8-6" />
        </svg>
        @break

    @case('cog')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <circle cx="12" cy="12" r="3" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 13.5a1.8 1.8 0 0 0 .36 1.98l.07.07a2.18 2.18 0 1 1-3.08 3.08l-.07-.07a1.8 1.8 0 0 0-1.98-.36 1.8 1.8 0 0 0-1.1 1.65V20a2.18 2.18 0 1 1-4.36 0v-.1a1.8 1.8 0 0 0-1.17-1.65 1.8 1.8 0 0 0-1.98.36l-.07.07a2.18 2.18 0 1 1-3.08-3.08l.07-.07a1.8 1.8 0 0 0 .36-1.98 1.8 1.8 0 0 0-1.65-1.1H4a2.18 2.18 0 1 1 0-4.36h.1a1.8 1.8 0 0 0 1.65-1.17 1.8 1.8 0 0 0-.36-1.98l-.07-.07A2.18 2.18 0 1 1 8.4 3.42l.07.07a1.8 1.8 0 0 0 1.98.36H10.55a1.8 1.8 0 0 0 1.1-1.65V2a2.18 2.18 0 1 1 4.36 0v.1a1.8 1.8 0 0 0 1.1 1.65h.1a1.8 1.8 0 0 0 1.98-.36l.07-.07a2.18 2.18 0 1 1 3.08 3.08l-.07.07a1.8 1.8 0 0 0-.36 1.98v.1a1.8 1.8 0 0 0 1.65 1.1H22a2.18 2.18 0 1 1 0 4.36h-.1a1.8 1.8 0 0 0-1.65 1.1Z" />
        </svg>
        @break

    @case('cart')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <circle cx="9" cy="20" r="1.25" />
            <circle cx="18" cy="20" r="1.25" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 3h2l2.4 12.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L21 7.5H6" />
        </svg>
        @break

    @case('search')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <circle cx="10.5" cy="10.5" r="6.5" />
            <path stroke-linecap="round" d="m20 20-4.35-4.35" />
        </svg>
        @break

    @case('menu')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        @break

    @case('close')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
        </svg>
        @break

    @case('trash')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-.8 12.2a2 2 0 0 1-2 1.8H9.8a2 2 0 0 1-2-1.8L7 7h10Z" />
        </svg>
        @break

    @case('check')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
        </svg>
        @break

    @case('sparkles')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 3v3M11 15v3M3 9h3M15 9h3.5M5.5 5.5l2 2M14.5 11.5l2 2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 6c0 2.76-2.24 5-5 5 2.76 0 5 2.24 5 5 0-2.76 2.24-5 5-5-2.76 0-5-2.24-5-5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 15c0 1.66-1.34 3-3 3 1.66 0 3 1.34 3 3 0-1.66 1.34-3 3-3-1.66 0-3-1.34-3-3Z" />
        </svg>
        @break

    @case('gift')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <rect x="3.5" y="9.5" width="17" height="4" rx="0.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 13.5h15V20a1 1 0 0 1-1 1h-13a1 1 0 0 1-1-1v-6.5ZM12 9.5V21M12 9.5c-1-2.5-2.8-5-4.75-5-1.4 0-2.25.9-2.25 2 0 2 2.5 3 7 3Zm0 0c1-2.5 2.8-5 4.75-5 1.4 0 2.25.9 2.25 2 0 2-2.5 3-7 3Z" />
        </svg>
        @break

    @case('ribbon')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <circle cx="12" cy="7" r="3.25" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 9.5 5 20.5l5.5-2 1.5 2.5 1.5-2.5 5.5 2-4.5-11" />
        </svg>
        @break

    @case('receipt')
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5.5 3.5h13v17l-2.25-1.5-2.25 1.5-2.25-1.5-2.25 1.5-2.25-1.5-2.25 1.5v-17Z" />
            <path stroke-linecap="round" d="M8.5 8h7M8.5 11.5h7M8.5 15h4.5" />
        </svg>
        @break

    @default
        <svg {{ $attributes }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <circle cx="12" cy="12" r="9" />
        </svg>
@endswitch
