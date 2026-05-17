<?php

use App\Domains\Auth\Services\DeleteAccountService;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public string $password = '';

    public string $confirmation = '';

    public function deleteAccount(DeleteAccountService $service, Logout $logout): void
    {
        $user = Auth::user();

        $service->validateInputs($user, [
            'password' => $this->password,
            'confirmation' => $this->confirmation,
        ]);

        // Logout *before* delete: Auth::logout() cycles the remember_token via
        // $user->save(), which would re-insert the row if we delete first.
        $logout();
        $service->delete($user);

        $this->redirect('/', navigate: true);
    }

    public function hasPassword(): bool
    {
        return Auth::user()->password !== null;
    }
}; ?>

<div>
    <x-dashy.modal.trigger name="confirm-account-deletion">
        <x-dashy.button variant="danger" data-test="delete-account-button">
            {{ __('Delete account') }}
        </x-dashy.button>
    </x-dashy.modal.trigger>

    <x-dashy.modal name="confirm-account-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteAccount" class="space-y-6">
            <div>
                <x-dashy.heading size="lg">{{ __('Are you sure you want to delete your account?') }}</x-dashy.heading>
                <x-dashy.subheading>
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. This cannot be undone.') }}
                </x-dashy.subheading>
            </div>

            @if ($this->hasPassword())
                <x-dashy.input
                    wire:model="password"
                    :label="__('Password')"
                    type="password"
                    viewable
                    autocomplete="current-password"
                />
            @else
                <x-dashy.input
                    wire:model="confirmation"
                    :label="__('Type DELETE to confirm')"
                    type="text"
                    autocomplete="off"
                />
            @endif

            <div class="flex justify-end gap-2">
                <x-dashy.modal.close>
                    <x-dashy.button type="button" variant="filled">{{ __('Cancel') }}</x-dashy.button>
                </x-dashy.modal.close>
                <x-dashy.button variant="danger" type="submit" data-test="confirm-delete-account-button">
                    {{ __('Delete account') }}
                </x-dashy.button>
            </div>
        </form>
    </x-dashy.modal>
</div>
