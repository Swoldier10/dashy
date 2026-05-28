<?php

use App\Domains\Preferences\Actions\FindUserPreferenceAction;
use App\Domains\Preferences\Services\UpdateWorkingHoursService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    use DispatchesDashyUi;

    /**
     * @var array<string, list<array{start: string, end: string}>>
     */
    public array $hours = [];

    public function mount(FindUserPreferenceAction $find): void
    {
        $pref = $find->execute(Auth::id(), UpdateWorkingHoursService::PREFERENCE_KEY);
        $stored = is_array($pref?->value) ? $pref->value : [];

        foreach (UpdateWorkingHoursService::DAYS as $day) {
            $ranges = $stored[$day] ?? [];

            if (! is_array($ranges)) {
                $ranges = [];
            }

            $this->hours[$day] = array_values(array_filter(array_map(
                static fn ($range): ?array => is_array($range) && isset($range['start'], $range['end'])
                    ? ['start' => (string) $range['start'], 'end' => (string) $range['end']]
                    : null,
                $ranges,
            )));
        }
    }

    public function addRange(string $day): void
    {
        if (! in_array($day, UpdateWorkingHoursService::DAYS, true)) {
            return;
        }

        $this->hours[$day][] = ['start' => '09:00', 'end' => '17:00'];
    }

    public function removeRange(string $day, int $index): void
    {
        if (! isset($this->hours[$day][$index])) {
            return;
        }

        unset($this->hours[$day][$index]);
        $this->hours[$day] = array_values($this->hours[$day]);
    }

    public function save(UpdateWorkingHoursService $service): void
    {
        $this->hours = $service->execute(Auth::user(), $this->hours);

        $this->toast('success', __('Working hours updated.'));
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function daysOfWeek(): array
    {
        return [
            'monday' => __('Monday'),
            'tuesday' => __('Tuesday'),
            'wednesday' => __('Wednesday'),
            'thursday' => __('Thursday'),
            'friday' => __('Friday'),
            'saturday' => __('Saturday'),
            'sunday' => __('Sunday'),
        ];
    }
}; ?>

<div>
    <section class="dashy-settings-section">
        <div class="dashy-settings-section-head">
            <h3>{{ __('Working Hours') }}</h3>
            <p>{{ __('Define when you are available each day of the week. Leave a day with no ranges to mark it as off.') }}</p>
        </div>

        <form wire:submit="save">
            @foreach ($this->daysOfWeek as $day => $label)
                @php
                    $rangeCount = count($hours[$day] ?? []);
                @endphp
                <div
                    class="border-t py-3.5 first:border-t-0"
                    style="border-color: var(--border);"
                    wire:key="working-day-{{ $day }}"
                    data-test="working-hours-day-{{ $day }}"
                >
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="row-label-text">{{ $label }}</span>
                            <span class="row-label-desc" data-test="working-hours-day-summary-{{ $day }}">
                                @if ($rangeCount === 0)
                                    {{ __('Off') }}
                                @else
                                    {{ trans_choice('{1} :count range|[2,*] :count ranges', $rangeCount, ['count' => $rangeCount]) }}
                                @endif
                            </span>
                        </div>
                        <button
                            type="button"
                            wire:click="addRange('{{ $day }}')"
                            class="dashy-btn dashy-btn--ghost dashy-btn--sm"
                            data-test="add-range-{{ $day }}"
                        >
                            <x-dashy.icon name="plus" class="size-3.5" />
                            <span>{{ __('Add range') }}</span>
                        </button>
                    </div>

                    @if ($rangeCount > 0)
                        <div class="mt-3 flex flex-col gap-2">
                            @foreach ($hours[$day] as $i => $range)
                                <div
                                    class="flex flex-wrap items-center gap-2"
                                    wire:key="working-range-{{ $day }}-{{ $i }}"
                                >
                                    <input
                                        type="time"
                                        wire:model="hours.{{ $day }}.{{ $i }}.start"
                                        class="dashy-input"
                                        style="width: auto; min-width: 0;"
                                        aria-label="{{ __(':day start time', ['day' => $label]) }}"
                                        data-test="range-start-{{ $day }}-{{ $i }}"
                                    />
                                    <span aria-hidden="true" style="color: var(--ink-muted);">→</span>
                                    <input
                                        type="time"
                                        wire:model="hours.{{ $day }}.{{ $i }}.end"
                                        class="dashy-input"
                                        style="width: auto; min-width: 0;"
                                        aria-label="{{ __(':day end time', ['day' => $label]) }}"
                                        data-test="range-end-{{ $day }}-{{ $i }}"
                                    />
                                    <button
                                        type="button"
                                        wire:click="removeRange('{{ $day }}', {{ $i }})"
                                        class="dashy-btn dashy-btn--ghost dashy-btn--sm"
                                        style="color: var(--ink-muted);"
                                        aria-label="{{ __('Remove range') }}"
                                        data-test="remove-range-{{ $day }}-{{ $i }}"
                                    >
                                        <x-dashy.icon name="x-mark" class="size-4" />
                                    </button>
                                </div>
                                @error('hours.'.$day.'.'.$i.'.start')
                                    <p class="dashy-error">{{ $message }}</p>
                                @enderror
                                @error('hours.'.$day.'.'.$i.'.end')
                                    <p class="dashy-error">{{ $message }}</p>
                                @enderror
                            @endforeach
                            @error('hours.'.$day)
                                <p class="dashy-error">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            @endforeach

            @error('hours')
                <p class="dashy-error mt-3">{{ $message }}</p>
            @enderror

            <div class="flex justify-end pt-4">
                <x-dashy.button
                    variant="primary"
                    class="dashy-btn--sm"
                    type="submit"
                    data-test="save-working-hours-button"
                >
                    {{ __('Save') }}
                </x-dashy.button>
            </div>
        </form>
    </section>
</div>
