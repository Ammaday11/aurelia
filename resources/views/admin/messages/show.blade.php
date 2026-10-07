<x-app-layout title="Message">

    <a href="{{ route('admin.messages.index') }}" class="text-sm text-mocha hover:underline">&larr; All Messages</a>

    <div class="card mt-4 p-6 sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-blush pb-5">
            <div>
                <h2 class="font-serif text-xl text-espresso">{{ $message->subject ?: '(no subject)' }}</h2>
                <p class="mt-1 text-sm text-espresso/70">
                    From <span class="font-semibold text-espresso">{{ $message->name }}</span>
                    &lt;<a href="mailto:{{ $message->email }}" class="hover:underline">{{ $message->email }}</a>&gt;
                </p>
                @if ($message->phone)
                    <p class="text-sm text-espresso/70">{{ $message->phone }}</p>
                @endif
            </div>
            <p class="text-sm text-espresso/60">{{ $message->created_at->format('d M Y, g:ia') }}</p>
        </div>

        <p class="mt-6 whitespace-pre-line text-espresso/90">{{ $message->message }}</p>

        <div class="mt-8 flex gap-3 border-t border-blush pt-6">
            <a href="mailto:{{ $message->email }}" class="btn-primary">Reply by Email</a>
            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Delete this message?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-outline">Delete</button>
            </form>
        </div>
    </div>

</x-app-layout>
