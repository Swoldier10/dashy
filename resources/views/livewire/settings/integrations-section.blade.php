<?php

use App\Domains\Auth\Services\DisconnectGoogleService;
use App\Domains\Codex\Actions\FindCodexConnectionForUserAction;
use App\Domains\Codex\Services\DisconnectCodexService;
use App\Domains\GoogleCalendar\Actions\FindGoogleCalendarConnectionForUserAction;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Services\DisconnectGoogleCalendarService;
use App\Domains\GoogleCalendar\Services\SyncGoogleCalendarService;
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
        $status = session('status');

        if ($status === 'codex-connected') {
            $this->toast('success', __('Codex connected.'));
        }

        if ($status === 'google-calendar-connected') {
            $this->toast('success', __('Google Calendar connected. The first sync is running in the background.'));
        }

        if ($status === 'google-calendar-connect-failed') {
            $this->toast('danger', __('We could not connect Google Calendar. Please try again.'));
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

    public function disconnectGoogleCalendar(DisconnectGoogleCalendarService $service): void
    {
        $service->execute(Auth::user());

        $this->toast('success', __('Google Calendar disconnected.'));
    }

    public function syncGoogleCalendarNow(SyncGoogleCalendarService $sync): void
    {
        $outcome = $sync->execute(Auth::user(), manual: true);

        $this->toast(
            'success',
            __('Sync complete. :pulled in, :pushed out.', [
                'pulled' => $outcome->pulled,
                'pushed' => $outcome->pushed,
            ]),
        );
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
    public function googleCalendarConnection(): ?GoogleCalendarConnection
    {
        return app(FindGoogleCalendarConnectionForUserAction::class)->execute(Auth::user());
    }

    #[Computed]
    public function isGoogleCalendarConnected(): bool
    {
        return $this->googleCalendarConnection !== null;
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
                    <x-dashy.icon name="calendar-days" class="size-4" style="color: var(--ink-muted);" />
                </span>
                <div class="min-w-0">
                    <span class="row-label-text">{{ __('Google Calendar') }}</span>
                    <span class="row-label-desc">
                        @if ($this->isGoogleCalendarConnected)
                            {{ __('Linked to :email', ['email' => $this->googleCalendarConnection->account_email ?? __('your Google account')]) }}
                            ·
                            {{ __('Last synced :when', [
                                'when' => $this->googleCalendarConnection->last_synced_at?->diffForHumans() ?? __('never'),
                            ]) }}
                        @else
                            {{ __('Sync events and tasks both ways with your primary Google Calendar.') }}
                        @endif
                    </span>
                </div>
            </div>
            <div class="dashy-settings-row-value flex flex-wrap justify-start gap-2 sm:justify-end">
                @if ($this->isGoogleCalendarConnected)
                    <x-dashy.button
                        type="button"
                        variant="ghost"
                        class="dashy-btn--sm"
                        wire:click="syncGoogleCalendarNow"
                        data-test="sync-google-calendar-button"
                    >
                        {{ __('Sync now') }}
                    </x-dashy.button>
                    <x-dashy.button
                        type="button"
                        variant="ghost"
                        class="dashy-btn--sm"
                        wire:click="disconnectGoogleCalendar"
                        wire:confirm="{{ __('Disconnect Google Calendar? Your existing local events stay; future changes will no longer sync.') }}"
                        data-test="disconnect-google-calendar-button"
                    >
                        {{ __('Disconnect') }}
                    </x-dashy.button>
                @else
                    <x-dashy.button
                        :href="route('google-calendar.connect')"
                        variant="filled"
                        class="dashy-btn--sm"
                        data-test="connect-google-calendar-button"
                    >
                        {{ __('Connect Google Calendar') }}
                    </x-dashy.button>
                @endif
            </div>
        </div>
        @if ($this->isGoogleCalendarConnected && $this->googleCalendarConnection->last_sync_error_at !== null)
            <div
                class="-mt-2 flex flex-col gap-2 rounded-md p-3 text-xs sm:flex-row sm:items-center sm:justify-between"
                style="background-color: color-mix(in srgb, var(--state-error) 12%, transparent); color: var(--state-error);"
                data-test="google-calendar-error-banner"
            >
                <span>{{ $this->googleCalendarConnection->last_sync_error ?? __('Reconnect required.') }}</span>
                <x-dashy.button
                    :href="route('google-calendar.connect')"
                    variant="filled"
                    class="dashy-btn--sm"
                >
                    {{ __('Reconnect') }}
                </x-dashy.button>
            </div>
        @endif
        @error('google_calendar')
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
