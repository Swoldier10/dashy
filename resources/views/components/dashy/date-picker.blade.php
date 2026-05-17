@props([
    'name' => null,           // Livewire model name (or HTML name attribute)
    'label' => null,
    'placeholder' => null,
    'minDate' => null,        // YYYY-MM-DD
    'maxDate' => null,        // YYYY-MM-DD
    'onChange' => null,       // Livewire method to call after a pick (e.g. "saveTaskDetail")
    'errorKey' => null,
    'showError' => true,
    'testId' => null,         // honoured for any inline data-test we want
    'withTime' => false,      // when true, panel includes 24h HH:MM inputs and value is YYYY-MM-DDTHH:mm
    'minuteStep' => 5,        // step for the minute input (1, 5, 15…)
])

@php
    if (! $name) {
        foreach ($attributes->getAttributes() as $key => $value) {
            if (str_starts_with($key, 'wire:model')) {
                $name = is_string($value) ? $value : null;
                break;
            }
        }
    }

    $errorBagKey = $errorKey ?? $name;
    $idBase = 'dashy-date-' . str_replace(['.', '['], ['-', '-'], (string) ($name ?? uniqid()));
@endphp

<div class="grid gap-1.5">
    @if ($label)
        <x-dashy.label :for="$idBase">{{ $label }}</x-dashy.label>
    @endif

    <div
        x-data="dashyDatePicker({
            modelName: @js($name),
            minDate: @js($minDate),
            maxDate: @js($maxDate),
            onChange: @js($onChange),
            withTime: @js((bool) $withTime),
            minuteStep: @js((int) $minuteStep),
        })"
        x-init="init()"
        @keydown="keyDown($event)"
        @click.outside="close()"
        @keydown.escape.window="open && close()"
        class="dashy-date-picker"
    >
        {{-- Visible read-only field that opens the calendar. --}}
        <div class="relative">
            <input
                id="{{ $idBase }}"
                type="text"
                readonly
                x-model="display"
                :placeholder="placeholder || @js($placeholder ?? __('Pick a date'))"
                @click="toggle()"
                @keydown.enter.prevent="toggle()"
                @keydown.space.prevent="toggle()"
                @if ($testId) data-test="{{ $testId }}" @endif
                {{ $attributes->whereDoesntStartWith(['wire:model', 'class'])->merge(['class' => 'dashy-input cursor-pointer pr-10']) }}
            />
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2" style="color: var(--ink-muted);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="size-4">
                    <path d="M8 3v3M16 3v3M3.5 9.5h17M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/>
                </svg>
            </span>
        </div>

        {{-- Calendar panel. --}}
        <div
            x-show="open"
            x-cloak
            x-transition.opacity.duration.120ms
            class="dashy-date-panel dashy-pop-in"
            role="dialog"
            aria-label="{{ __('Choose a date') }}"
        >
            {{-- Header: month/year + navigation. --}}
            <div class="dashy-date-header">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-sm font-semibold" style="color: var(--ink);" x-text="MONTH_NAMES[month]"></span>
                    <span class="text-sm" style="color: var(--ink-muted);" x-text="year"></span>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        @click.stop="prevMonth()"
                        class="dashy-date-nav"
                        aria-label="{{ __('Previous month') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                            <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        @click.stop="nextMonth()"
                        class="dashy-date-nav"
                        aria-label="{{ __('Next month') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                            <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 1 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Weekday header. --}}
            <div class="dashy-date-grid mb-1">
                <template x-for="day in DAYS_SHORT" :key="day">
                    <div class="dashy-date-weekday" x-text="day"></div>
                </template>
            </div>

            {{-- Day cells. --}}
            <div class="dashy-date-grid">
                <template x-for="blank in blankdays" :key="'b' + blank">
                    <div></div>
                </template>
                <template x-for="d in noOfDays" :key="d">
                    <button
                        type="button"
                        @click.stop="select(d)"
                        :disabled="!isInRange(d)"
                        :tabindex="focused === d ? 0 : -1"
                        :data-active="focused === d"
                        :class="{
                            'dashy-date-cell--selected': isSelected(d),
                            'dashy-date-cell--today': isToday(d) && !isSelected(d),
                            'dashy-date-cell--disabled': !isInRange(d),
                        }"
                        class="dashy-date-cell"
                        x-text="d"
                    ></button>
                </template>
            </div>

            @if ($withTime)
                <div class="dashy-date-time-row">
                    <span class="dashy-date-time-label">{{ __('Time') }}</span>
                    <input
                        type="number"
                        min="0"
                        max="23"
                        step="1"
                        inputmode="numeric"
                        x-model.number="hour"
                        @input="onTimeInput()"
                        @blur="clampTime()"
                        @keydown.enter.prevent="clampTime(); close();"
                        class="dashy-date-time-input"
                        aria-label="{{ __('Hour') }}"
                    />
                    <span class="dashy-date-time-sep">:</span>
                    <input
                        type="number"
                        min="0"
                        max="59"
                        :step="minuteStep"
                        inputmode="numeric"
                        x-model.number="minute"
                        @input="onTimeInput()"
                        @blur="clampTime()"
                        @keydown.enter.prevent="clampTime(); close();"
                        class="dashy-date-time-input"
                        aria-label="{{ __('Minute') }}"
                    />
                </div>
            @endif

            {{-- Footer actions. --}}
            <div class="dashy-date-footer">
                <button
                    type="button"
                    @click.stop="select(new Date().getDate()); month = new Date().getMonth(); year = new Date().getFullYear(); recomputeGrid(); select(new Date().getDate());"
                    class="dashy-date-footer-btn"
                >{{ __('Today') }}</button>
                <button
                    type="button"
                    @click.stop="clear()"
                    class="dashy-date-footer-btn"
                    x-show="value"
                    x-cloak
                >{{ __('Clear') }}</button>
            </div>
        </div>
    </div>

    @if ($showError && $errorBagKey)
        <x-dashy.field-error :name="$errorBagKey" />
    @endif
</div>
