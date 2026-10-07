<x-app-layout title="Profile">
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="card p-4 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="card p-4 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="card p-4 sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
