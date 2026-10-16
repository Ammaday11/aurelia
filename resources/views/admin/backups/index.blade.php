@php
    $sections = [
        'daily' => 'Daily Backups',
        'weekly' => 'Weekly Backups',
        'monthly' => 'Monthly Backups',
        'manual' => 'Manual Backups',
        'pre-restore' => 'Safety Backups (taken automatically before a restore)',
    ];
@endphp

<x-app-layout title="Backups">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-espresso/70">Total storage used: {{ number_format($totalSize / 1048576, 1) }} MB</p>
            <p class="text-xs text-espresso/50">
                A backup contains the full database plus every uploaded photo. Daily backups run automatically and keep the last {{ config('backup.keep.daily') }};
                weekly keeps the last {{ config('backup.keep.weekly') }}; monthly keeps the last {{ config('backup.keep.monthly') }}.
            </p>
        </div>
        <form method="POST" action="{{ route('admin.backups.store') }}">
            @csrf
            <button type="submit" class="btn-primary">Backup Now</button>
        </form>
    </div>

    @foreach ($sections as $type => $label)
        @if ($type !== 'pre-restore' || $byType[$type]->isNotEmpty())
            <div class="card mb-6 overflow-x-auto">
                <div class="border-b border-blush px-5 py-3">
                    <h2 class="font-serif text-lg text-espresso">{{ $label }}</h2>
                </div>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-blush text-xs uppercase tracking-wide text-mocha">
                            <th class="px-5 py-3">Created</th>
                            <th class="px-5 py-3">Size</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blush">
                        @forelse ($byType[$type] as $backup)
                            <tr>
                                <td class="px-5 py-3 text-espresso">{{ $backup->createdAt->format('d M Y, g:ia') }}</td>
                                <td class="px-5 py-3 text-espresso/80">{{ $backup->humanSize() }}</td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('admin.backups.download', $backup->filename) }}" class="text-mocha hover:underline">Download</a>

                                        <form method="POST" action="{{ route('admin.backups.restore', $backup->filename) }}"
                                              onsubmit="return confirm('Restore from this backup? This replaces the current database and all uploaded photos with what\'s in this backup. A safety backup of the current state is taken first, but this still takes the site offline briefly. Are you sure?');">
                                            @csrf
                                            <button type="submit" class="text-espresso hover:underline">Restore</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.backups.destroy', $backup->filename) }}"
                                              onsubmit="return confirm('Delete this backup? This cannot be undone.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-6 text-center text-espresso/60">None yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach

    <div class="card p-6">
        <h2 class="font-serif text-lg text-espresso">Restore From an Uploaded Backup</h2>
        <p class="mt-1 text-sm text-espresso/60">
            Pick a backup .zip you previously downloaded from this screen (e.g. after moving to a new server) and restore from it directly.
        </p>

        <form method="POST" action="{{ route('admin.backups.restore-upload') }}" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-end gap-4"
              onsubmit="return confirm('Restore from this uploaded backup? This replaces the current database and all uploaded photos with what\'s in the file. A safety backup of the current state is taken first, but this still takes the site offline briefly. Are you sure?');">
            @csrf
            <div class="flex-1">
                <x-input-label for="backup" value="Backup File (.zip)" />
                <input type="file" id="backup" name="backup" accept=".zip"
                       class="input mt-1 w-full file:mr-4 file:rounded-full file:border-0 file:bg-mocha file:px-4 file:py-2 file:text-sm file:font-semibold file:text-beige-surface">
                <x-input-error :messages="$errors->get('backup')" class="mt-2" />
            </div>
            <button type="submit" class="btn-outline">Restore From File</button>
        </form>
    </div>

</x-app-layout>
