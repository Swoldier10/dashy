<?php

use App\Domains\Notifications\Enums\NotificationChannel;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Services\GetNotificationPreferencesService;
use App\Domains\Notifications\Services\UpdateNotificationPreferencesService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    use DispatchesDashyUi;

    /**
     * Complete per-type channel map (every enum case present — the service
     * merges stored choices over the enum defaults).
     *
     * @var array<string, array{email: bool, app: bool}>
     */
    public array $prefs = [];

    public function mount(GetNotificationPreferencesService $preferences): void
    {
        $this->prefs = $preferences->execute((int) Auth::id());
    }

    /**
     * Auto-save on toggle. For nested array properties Livewire passes the
     * changed element's path (e.g. "task_assigned.email") as $key.
     */
    public function updatedPrefs(mixed $value, string $key): void
    {
        [$type, $channel] = array_pad(explode('.', $key, 2), 2, null);

        if (NotificationType::tryFrom((string) $type) === null || NotificationChannel::tryFrom((string) $channel) === null) {
            return;
        }

        $this->prefs = app(UpdateNotificationPreferencesService::class)->execute(Auth::user(), $this->prefs);

        $this->toast('success', __('Notification preferences updated.'));
    }

    /**
     * @return array<string, list<NotificationType>> keyed by category label
     */
    #[Computed]
    public function groupedTypes(): array
    {
        $grouped = [];

        foreach (NotificationType::cases() as $type) {
            $grouped[$type->category()->label()][] = $type;
        }

        return $grouped;
    }
}; ?>

<div>
    <section class="dashy-settings-section">
        <div class="dashy-settings-section-head">
            <h3>{{ __('Notifications') }}</h3>
            <p>{{ __('Choose how you want to be notified for each activity — by e-mail, in the app, or both. Changes are saved automatically.') }}</p>
        </div>

        <div class="mt-4" data-test="notifications-matrix">
            <div class="dashy-notif-head" aria-hidden="true">
                <span>{{ __('Notification') }}</span>
                <span>{{ __('E-mail') }}</span>
                <span>{{ __('App') }}</span>
            </div>

            @foreach ($this->groupedTypes as $categoryLabel => $types)
                <p class="dashy-notif-cat" wire:key="notif-cat-{{ $loop->index }}">{{ $categoryLabel }}</p>

                @foreach ($types as $type)
                    <div
                        class="dashy-notif-row"
                        wire:key="notif-row-{{ $type->value }}"
                        data-test="notif-row-{{ $type->value }}"
                    >
                        <div class="min-w-0 py-2">
                            <span class="row-label-text">{{ $type->label() }}</span>
                            <span class="row-label-desc">{{ $type->description() }}</span>
                        </div>
                        <div class="dashy-notif-cell" data-test="notif-cell-{{ $type->value }}-email">
                            <x-dashy.checkbox
                                wire:model.live="prefs.{{ $type->value }}.email"
                                aria-label="{{ __(':type — via e-mail', ['type' => $type->label()]) }}"
                            />
                        </div>
                        <div class="dashy-notif-cell" data-test="notif-cell-{{ $type->value }}-app">
                            <x-dashy.checkbox
                                wire:model.live="prefs.{{ $type->value }}.app"
                                aria-label="{{ __(':type — in the app', ['type' => $type->label()]) }}"
                            />
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </section>
</div>
