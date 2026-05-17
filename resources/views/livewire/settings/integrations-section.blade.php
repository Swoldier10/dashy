<?php

use App\Domains\Auth\Services\DisconnectGoogleService;
use App\Domains\Codex\Actions\FindCodexConnectionForUserAction;
use App\Domains\Codex\Services\DisconnectCodexService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use DispatchesDashyUi;

    public function mount(): void
    {
        if (session('status') === 'codex-connected') {
            $this->toast('success', __('Codex connected.'));
        }
    }

    public function disconnectGoogle(DisconnectGoogleService $service): void
    {
        $service->execute(Auth::user());

        $this->toast('success', __('Google account disconnected.'));
    }

    public function disconnectCodex(DisconnectCodexService $service): void
    {
        $service->execute(Auth::user());

        $this->toast('success', __('Codex disconnected.'));
    }

    #[On('codex-connected')]
    public function refreshCodexConnection(): void
    {
        // Empty body — listener triggers re-render so isCodexConnected re-evaluates.
    }

    #[Computed]
    public function isGoogleConnected(): bool
    {
        return Auth::user()->google_id !== null;
    }

    #[Computed]
    public function isCodexConnected(): bool
    {
        return app(FindCodexConnectionForUserAction::class)->execute(Auth::user()) !== null;
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
            <h3>{{ __('Connected accounts') }}</h3>
            <p>{{ __('Sign-in providers and integrations linked to your account.') }}</p>
        </div>

        <div class="dashy-settings-row">
            <div class="dashy-settings-row-label flex items-center gap-3">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg" style="background-color: var(--surface-2);">
                    <x-dashy.icon name="globe-alt" class="size-4" style="color: var(--ink-muted);" />
                </span>
                <div class="min-w-0">
                    <span class="row-label-text">Google</span>
                    <span class="row-label-desc">
                        {{ $this->isGoogleConnected ? __('Linked') : __('Not linked') }}
                        @if ($this->isGoogleConnected && ! $this->hasPassword)
                            · {{ __('Set a password before disconnecting.') }}
                        @endif
                    </span>
                </div>
            </div>
            <div class="dashy-settings-row-value flex justify-start sm:justify-end">
                @if ($this->isGoogleConnected)
                    <x-dashy.button
                        type="button"
                        variant="ghost"
                        class="dashy-btn--sm"
                        wire:click="disconnectGoogle"
                        :disabled="!$this->hasPassword"
                        data-test="disconnect-google-button"
                    >
                        {{ __('Disconnect') }}
                    </x-dashy.button>
                @else
                    <x-dashy.button :href="route('auth.google.redirect')" variant="filled" class="dashy-btn--sm">
                        {{ __('Link Google') }}
                    </x-dashy.button>
                @endif
            </div>
        </div>
        @error('google')
            <p class="-mt-2 text-xs" style="color: var(--state-error);">{{ $message }}</p>
        @enderror

        <div class="dashy-settings-row">
            <div class="dashy-settings-row-label flex items-center gap-3">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg" style="background-color: var(--surface-2);">
                    <x-dashy.icon name="sparkles" class="size-4" style="color: var(--accent);" />
                </span>
                <div class="min-w-0">
                    <span class="row-label-text">Codex</span>
                    <span class="row-label-desc">{{ $this->isCodexConnected ? __('Linked') : __('Not linked') }}</span>
                </div>
            </div>
            <div class="dashy-settings-row-value flex justify-start sm:justify-end">
                @if ($this->isCodexConnected)
                    <x-dashy.button
                        type="button"
                        variant="ghost"
                        class="dashy-btn--sm"
                        wire:click="disconnectCodex"
                        data-test="disconnect-codex-button"
                    >
                        {{ __('Disconnect') }}
                    </x-dashy.button>
                @else
                    <x-dashy.button
                        type="button"
                        variant="filled"
                        class="dashy-btn--sm"
                        icon="link"
                        x-on:click="$dispatch('open-connect-codex')"
                        data-test="connect-codex-from-profile"
                    >
                        {{ __('Link Codex') }}
                    </x-dashy.button>
                @endif
            </div>
        </div>
        @error('codex')
            <p class="-mt-2 text-xs" style="color: var(--state-error);">{{ $message }}</p>
        @enderror
    </section>
</div>
