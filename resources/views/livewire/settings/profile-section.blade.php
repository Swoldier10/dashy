<?php

use App\Domains\Auth\Enums\Salutation;
use App\Domains\Auth\Services\AvatarService;
use App\Domains\Auth\Services\UpdateProfileInformationService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component
{
    use DispatchesDashyUi;

    use WithFileUploads;

    public ?string $salutation = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public ?TemporaryUploadedFile $newAvatar = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->salutation = $user->salutation?->value;
        $this->first_name = (string) ($user->first_name ?? '');
        $this->last_name = (string) ($user->last_name ?? '');
        $this->email = $user->email;
    }

    public function updateProfile(UpdateProfileInformationService $service): void
    {
        $service->execute(Auth::user(), [
            'salutation' => $this->salutation,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
        ]);

        $this->toast('success', __('Profile updated.'));
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        $this->toast('info', __('A new verification link has been sent to your email address.'));
    }

    public function updatedNewAvatar(AvatarService $service): void
    {
        if ($this->newAvatar === null) {
            return;
        }

        try {
            $service->upload(Auth::user(), $this->newAvatar);
        } catch (ValidationException $e) {
            $this->newAvatar = null;
            throw $e;
        }

        $this->newAvatar = null;

        $this->toast('success', __('Avatar updated.'));
    }

    public function removeAvatar(AvatarService $service): void
    {
        $service->remove(Auth::user());

        $this->toast('success', __('Avatar removed.'));
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        $user = Auth::user();

        return $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function salutationOptions(): array
    {
        return Salutation::options();
    }

    #[Computed]
    public function avatarUrl(): ?string
    {
        return Auth::user()->avatar;
    }
}; ?>

<div>
    <section class="dashy-settings-section">
        <div class="dashy-settings-section-head">
            <h3>{{ __('Profile information') }}</h3>
            <p>{{ __('Your name, email, and avatar shown across Dashy.') }}</p>
        </div>

        {{-- Avatar row --}}
        <div class="dashy-settings-row">
            <div class="dashy-settings-row-label flex items-center gap-3">
                <x-dashy.avatar
                    size="md"
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    :src="$this->avatarUrl"
                />
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium" style="color: var(--ink);">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs" style="color: var(--ink-muted);">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <div class="dashy-settings-row-value flex flex-wrap items-center gap-2 sm:justify-end">
                <input
                    type="file"
                    wire:model="newAvatar"
                    id="settings-avatar-input"
                    class="sr-only"
                    accept="image/jpeg,image/png,image/webp"
                />
                <x-dashy.button
                    type="button"
                    variant="filled"
                    class="dashy-btn--sm"
                    x-on:click="document.getElementById('settings-avatar-input').click()"
                    data-test="upload-avatar-button"
                >
                    {{ __('Upload new') }}
                </x-dashy.button>
                @if ($this->avatarUrl)
                    <x-dashy.button
                        type="button"
                        variant="ghost"
                        class="dashy-btn--sm"
                        wire:click="removeAvatar"
                        data-test="remove-avatar-button"
                    >
                        {{ __('Remove') }}
                    </x-dashy.button>
                @endif
            </div>
        </div>
        @error('avatar')
            <p class="-mt-2 text-xs" style="color: var(--state-error);">{{ $message }}</p>
        @enderror
        @error('newAvatar')
            <p class="-mt-2 text-xs" style="color: var(--state-error);">{{ $message }}</p>
        @enderror

        {{-- Identity form --}}
        <form wire:submit="updateProfile">
            <div class="dashy-settings-row">
                <div class="dashy-settings-row-label">
                    <span class="row-label-text">{{ __('Salutation') }}</span>
                    <span class="row-label-desc">{{ __('How we address you across Dashy.') }}</span>
                </div>
                <div class="dashy-settings-row-value">
                    <x-dashy.select wire:model="salutation">
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($this->salutationOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-dashy.select>
                </div>
            </div>

            <div class="dashy-settings-row">
                <div class="dashy-settings-row-label">
                    <span class="row-label-text">{{ __('First name') }}</span>
                    <span class="row-label-desc">{{ __('Your given name.') }}</span>
                </div>
                <div class="dashy-settings-row-value">
                    <x-dashy.input wire:model="first_name" required autocomplete="given-name" />
                </div>
            </div>

            <div class="dashy-settings-row">
                <div class="dashy-settings-row-label">
                    <span class="row-label-text">{{ __('Last name') }}</span>
                    <span class="row-label-desc">{{ __('Your family name.') }}</span>
                </div>
                <div class="dashy-settings-row-value">
                    <x-dashy.input wire:model="last_name" required autocomplete="family-name" />
                </div>
            </div>

            <div class="dashy-settings-row">
                <div class="dashy-settings-row-label">
                    <span class="row-label-text">{{ __('Email') }}</span>
                    <span class="row-label-desc">
                        @if ($this->hasUnverifiedEmail)
                            {{ __('Unverified —') }}
                            <button type="button" wire:click.prevent="resendVerificationNotification" class="underline" style="color: var(--blue);">
                                {{ __('resend verification') }}
                            </button>
                        @else
                            {{ __('Where we send account email.') }}
                        @endif
                    </span>
                </div>
                <div class="dashy-settings-row-value">
                    <x-dashy.input wire:model="email" type="email" required autocomplete="email" />
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <x-dashy.button variant="primary" class="dashy-btn--sm" type="submit" data-test="update-profile-button">
                    {{ __('Save') }}
                </x-dashy.button>
            </div>
        </form>
    </section>
</div>
