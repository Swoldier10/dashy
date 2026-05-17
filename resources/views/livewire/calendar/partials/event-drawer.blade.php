@php
    use App\Domains\Calendar\Enums\EventColor;
    use App\Domains\Calendar\Enums\RecurrenceFreq;

    $colors = [
        EventColor::Danube,
        EventColor::Torea,
        EventColor::Shilo,
        EventColor::Success,
        EventColor::Warning,
        EventColor::Error,
    ];

    $recurrenceOptions = [
        RecurrenceFreq::None,
        RecurrenceFreq::Daily,
        RecurrenceFreq::Weekly,
        RecurrenceFreq::Monthly,
        RecurrenceFreq::Yearly,
    ];

    $isCreate = $drawerCreateMode;
@endphp

<x-dashy.drawer name="calendar-event-detail" side="right" size="md" focusable>
    <div
        wire:key="event-drawer-{{ $detailEventId ?? 'new' }}-{{ $isCreate ? 'create' : 'edit' }}"
        class="flex h-full flex-col"
    >
        <form
            wire:submit.prevent="{{ $isCreate ? 'submitCreate' : 'submitEdit' }}"
            class="flex h-full flex-col gap-5 p-6"
            data-test="calendar-event-form"
        >
            <header class="flex items-start justify-between gap-3">
                <h2 class="font-display text-xl text-[var(--ink)]">
                    {{ $isCreate ? __('New event') : __('Edit event') }}
                </h2>
                <button
                    type="button"
                    @click="$store.modals.close('calendar-event-detail')"
                    class="inline-flex size-9 items-center justify-center rounded-md text-[var(--ink-muted)] hover:bg-[var(--surface-2)] hover:text-[var(--ink)]"
                    aria-label="{{ __('Close') }}"
                >
                    <x-dashy.icon name="x-mark" class="size-5" />
                </button>
            </header>

            <x-dashy.input
                wire:model.live="formTitle"
                name="formTitle"
                :label="__('Title')"
                :placeholder="__('What is happening?')"
                maxlength="200"
                required
                data-test="calendar-event-title"
            />

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <x-dashy.date-picker
                    wire:model.live="formStartAt"
                    name="formStartAt"
                    :label="__('Start')"
                    with-time
                    test-id="calendar-event-start"
                />

                <x-dashy.date-picker
                    wire:model.live="formEndAt"
                    name="formEndAt"
                    :label="__('End')"
                    with-time
                    test-id="calendar-event-end"
                />
            </div>

            <label class="flex items-center gap-2">
                <input
                    type="checkbox"
                    wire:model.live="formIsAllDay"
                    class="size-4 rounded border-[var(--border)] bg-[var(--bg-deep)] text-brand-danube focus:ring-brand-danube"
                />
                <span class="text-sm text-[var(--ink)]">{{ __('All-day event') }}</span>
            </label>

            <div class="grid gap-2">
                <span class="text-xs font-medium uppercase tracking-wider text-[var(--ink-dim)]">{{ __('Color') }}</span>
                <div class="flex flex-wrap gap-2">
                    @foreach ($colors as $color)
                        <button
                            type="button"
                            wire:click="$set('formColor', '{{ $color->value }}')"
                            @class([
                                'size-9 rounded-full border-2 transition focus:outline-none focus:ring-2 focus:ring-brand-danube',
                                'border-[var(--ink)]' => $formColor === $color->value,
                                'border-transparent hover:border-[var(--ink-muted)]' => $formColor !== $color->value,
                            ])
                            style="background-color: var({{ $color->colorVar() }});"
                            aria-label="{{ $color->label() }}"
                            data-test="calendar-color-{{ $color->value }}"
                        ></button>
                    @endforeach
                </div>
            </div>

            <x-dashy.input
                wire:model.live="formLocation"
                name="formLocation"
                :label="__('Location')"
                :placeholder="__('Optional')"
                maxlength="200"
            />

            <label class="grid gap-1.5">
                <span class="text-xs font-medium uppercase tracking-wider text-[var(--ink-dim)]">{{ __('Description') }}</span>
                <textarea
                    wire:model.live="formDescription"
                    rows="3"
                    class="rounded-md border border-[var(--border)] bg-[var(--bg-deep)] p-3 text-sm text-[var(--ink)] focus:border-brand-danube focus:outline-none"
                ></textarea>
            </label>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <label class="grid gap-1.5">
                    <span class="text-xs font-medium uppercase tracking-wider text-[var(--ink-dim)]">{{ __('Repeat') }}</span>
                    <select
                        wire:model.live="formRecurrenceFreq"
                        class="h-11 rounded-md border border-[var(--border)] bg-[var(--bg-deep)] px-3 text-sm text-[var(--ink)] focus:border-brand-danube focus:outline-none lg:h-9"
                    >
                        @foreach ($recurrenceOptions as $option)
                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                        @endforeach
                    </select>
                </label>

                @if ($formRecurrenceFreq !== 'none')
                    <label class="grid gap-1.5">
                        <span class="text-xs font-medium uppercase tracking-wider text-[var(--ink-dim)]">{{ __('Repeat until') }}</span>
                        <input
                            type="date"
                            wire:model.live="formRecurrenceUntil"
                            class="h-11 rounded-md border border-[var(--border)] bg-[var(--bg-deep)] px-3 text-sm text-[var(--ink)] focus:border-brand-danube focus:outline-none lg:h-9"
                            data-test="calendar-event-recurrence-until"
                        />
                    </label>
                @endif
            </div>

            <footer class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-4">
                @if (! $isCreate)
                    <x-dashy.button
                        type="button"
                        wire:click="deleteEvent"
                        wire:confirm="{{ __('Delete this event?') }}"
                        variant="ghost"
                        icon="trash"
                        data-test="calendar-event-delete"
                    >
                        {{ __('Delete') }}
                    </x-dashy.button>
                @else
                    <span></span>
                @endif

                <div class="flex items-center gap-2">
                    <x-dashy.button
                        type="button"
                        @click="$store.modals.close('calendar-event-detail')"
                        variant="ghost"
                    >
                        {{ __('Cancel') }}
                    </x-dashy.button>

                    <x-dashy.button
                        type="submit"
                        variant="primary"
                        data-test="calendar-event-submit"
                    >
                        {{ $isCreate ? __('Create') : __('Save') }}
                    </x-dashy.button>
                </div>
            </footer>
        </form>
    </div>
</x-dashy.drawer>
