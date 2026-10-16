@csrf
@isset($editUser) @method('PUT') @endisset

<div class="mx-auto max-w-lg">
    <div class="card space-y-5 p-6">
        <div>
            <x-input-label for="name" value="Name" />
            <x-text-input id="name" name="name" class="mt-1 w-full" value="{{ old('name', $editUser->name ?? '') }}" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 w-full" value="{{ old('email', $editUser->email ?? '') }}" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="isset($editUser) ? 'New Password (optional)' : 'Password'" />
            <x-text-input id="password" name="password" type="password" class="mt-1 w-full" :required="! isset($editUser)" />
            @if (isset($editUser))
                <p class="mt-1 text-xs text-espresso/60">Leave blank to keep the current password.</p>
            @endif
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirm Password" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 w-full" :required="! isset($editUser)" />
        </div>
    </div>

    <button type="submit" class="btn-primary mt-6 w-full">{{ isset($editUser) ? 'Update User' : 'Create User' }}</button>
</div>
