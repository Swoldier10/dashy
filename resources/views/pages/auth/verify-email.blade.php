<x-layouts::auth :title="__('Email verification')">
    <div class="mt-4 flex flex-col gap-6">
        <x-dashy.text class="text-center">
            {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
        </x-dashy.text>

        @if (session('status') == 'verification-link-sent')
            <x-dashy.text class="text-center font-medium !dark:text-green-400 !text-green-600">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </x-dashy.text>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-dashy.button type="submit" variant="primary" class="w-full">
                    {{ __('Resend verification email') }}
                </x-dashy.button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dashy.button variant="ghost" type="submit" class="text-sm cursor-pointer" data-test="logout-button">
                    {{ __('Log out') }}
                </x-dashy.button>
            </form>
        </div>
    </div>
</x-layouts::auth>
