<?php

use App\Domains\Auth\Services\UpdatePasswordService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    use DispatchesDashyUi;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function updatePassword(UpdatePasswordService $service): void
    {
        try {
            $service->execute(Auth::user(), [
                'current_password' => $this->current_password,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->toast('success', __('Password updated.'));
    }

    #[Computed]
    public function hasPassword(): bool
    {
        return Auth::user()->password !== null;
    }
}; ?>

<div>
    <section class="dashy-settings-section">
        <div class="dashy-settings-section-head">
            <h3>{{ $this->hasPassword ? __('Change password') : __('Set password') }}</h3>
            <p>
                {{ $this->hasPassword
                    ? __('Use a long, random password to keep your account secure.')
                    : __('Set a password so you can sign in without Google.') }}
            </p>
        </div>

        <form wire:submit="updatePassword">
            @if ($this->hasPassword)
                <div class="dashy-settings-row">
                    <div class="dashy-settings-row-label">
                        <span class="row-label-text">{{ __('Current password') }}</span>
                        <span class="row-label-desc">{{ __('Confirm it’s you.') }}</span>
                    </div>
                    <div class="dashy-settings-row-value">
                        <x-dashy.input
                            wire:model="current_password"
                            type="password"
                            autocomplete="current-password"
                            viewable
                            required
                        />
                    </div>
                </div>
            @endif

            <div class="dashy-settings-row">
                <div class="dashy-settings-row-label">
                    <span class="row-label-text">{{ __('New password') }}</span>
                    <span class="row-label-desc">{{ __('At least 8 characters.') }}</span>
                </div>
                <div class="dashy-settings-row-value">
                    <x-dashy.input
                        wire:model="password"
                        type="password"
                        autocomplete="new-password"
                        viewable
                        required
                    />
                </div>
            </div>

            <div class="dashy-settings-row">
                <div class="dashy-settings-row-label">
                    <span class="row-label-text">{{ __('Confirm new password') }}</span>
                    <span class="row-label-desc">{{ __('Re-enter to confirm.') }}</span>
                </div>
                <div class="dashy-settings-row-value">
                    <x-dashy.input
                        wire:model="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        viewable
                        required
                    />
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <x-dashy.button variant="primary" class="dashy-btn--sm" type="submit" data-test="update-password-button">
                    {{ __('Save') }}
                </x-dashy.button>
            </div>
        </form>
    </section>

    <section class="dashy-settings-section">
        <div class="dashy-settings-section-head">
            <h3 style="color: var(--state-error);">{{ __('Danger zone') }}</h3>
            <p>{{ __('Permanently delete your account and all associated data. This cannot be undone.') }}</p>
        </div>

        <div class="dashy-settings-row">
            <div class="dashy-settings-row-label">
                <span class="row-label-text">{{ __('Delete account') }}</span>
                <span class="row-label-desc">{{ __('Remove your account, projects, chats, and uploaded files.') }}</span>
            </div>
            <div class="dashy-settings-row-value flex justify-start sm:justify-end">
                <livewire:settings.delete-account-modal />
            </div>
        </div>
    </section>
</div>
