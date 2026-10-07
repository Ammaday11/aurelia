<x-guest-layout>
    <div class="mb-4 text-sm text-espresso/70">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-lg bg-sand/20 border border-sand px-4 py-3 text-sm font-semibold text-espresso">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-espresso/70 hover:text-espresso rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sand">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
