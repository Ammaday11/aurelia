<x-app-layout title="Users">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <p class="text-sm text-espresso/70">{{ $users->count() }} {{ Str::plural('user', $users->count()) }}</p>
            <p class="text-xs text-espresso/50">Staff accounts with access to this admin panel. Public self-registration is disabled — new accounts are created here.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn-primary">+ New User</a>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-blush text-xs uppercase tracking-wide text-mocha">
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3">Joined</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-blush">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-5 py-3">
                            <span class="font-semibold text-espresso">{{ $user->name }}</span>
                            @if (auth()->user()->is($user))
                                <span class="badge ml-2 bg-sand/30 text-espresso">You</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-espresso/80">{{ $user->email }}</td>
                        <td class="px-5 py-3 text-espresso/80">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-mocha hover:underline">Edit</a>
                                @if (! auth()->user()->is($user) && $users->count() > 1)
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('Delete this user? They will lose access immediately. This cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-espresso/60">No users yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-app-layout>
