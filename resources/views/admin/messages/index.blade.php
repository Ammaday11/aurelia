<x-app-layout title="Messages">

    <div class="card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-blush text-xs uppercase tracking-wide text-mocha">
                    <th class="px-5 py-3">From</th>
                    <th class="px-5 py-3">Subject</th>
                    <th class="px-5 py-3">Received</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-blush">
                @forelse ($messages as $message)
                    <tr class="{{ $message->is_read ? '' : 'bg-sand/5' }}">
                        <td class="px-5 py-3">
                            <p class="font-semibold text-espresso">{{ $message->name }}</p>
                            <p class="text-xs text-espresso/60">{{ $message->email }}</p>
                        </td>
                        <td class="px-5 py-3 text-espresso/80">{{ $message->subject ?: '(no subject)' }}</td>
                        <td class="px-5 py-3 text-espresso/60">{{ $message->created_at->diffForHumans() }}</td>
                        <td class="px-5 py-3">
                            @if ($message->is_read)
                                <span class="badge bg-blush text-espresso/60">Read</span>
                            @else
                                <span class="badge bg-mocha text-beige-surface">New</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.messages.show', $message) }}" class="text-mocha hover:underline">View</a>
                                <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Delete this message?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-espresso/60">No messages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $messages->links() }}
    </div>

</x-app-layout>
