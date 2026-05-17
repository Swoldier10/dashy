@php
    /**
     * @var \App\Domains\Chat\Models\Message $message
     * @var array<string, mixed> $card
     */
    $status = $card['status'] ?? 'pending';
    $isPending = $status === 'pending';
    $editKey = 'toolCallEdits.'.$message->id;
    $validationErrors = (array) ($card['validation_errors'] ?? []);
    $args = is_array($card['arguments'] ?? null) ? $card['arguments'] : [];
    $colors = (array) ($card['available_colors'] ?? []);
    $recurrences = (array) ($card['available_recurrence_freqs'] ?? []);
    $selectedColor = (string) ($args['color'] ?? 'danube');
    $selectedRecurrence = (string) ($args['recurrence_freq'] ?? 'none');
@endphp

<div
    class="mt-3 overflow-hidden rounded-2xl border"
    style="border-color: var(--border-mid); background-color: var(--surface-2);"
    data-test="tool-call-card"
    data-tool="create_event"
    data-status="{{ $status }}"
>
    {{-- Header --}}
    <div
        class="flex items-center gap-2 border-b px-4 py-2.5 text-xs uppercase tracking-wide"
        style="border-color: var(--border); color: var(--ink-muted);"
    >
        <x-dashy.icon name="calendar-days" class="size-4" style="color: var(--blue);" />
        @if ($isPending)
            {{ __('New event — review before creating') }}
        @elseif ($status === 'created')
            <span style="color: var(--state-success);">{{ __('Event created') }}</span>
        @elseif ($status === 'discarded')
            {{ __('Discarded') }}
        @elseif ($status === 'failed')
            <span style="color: var(--state-error);">{{ __('Could not prepare event') }}</span>
        @endif
    </div>

    @if ($status === 'failed')
        <div class="px-4 py-4">
            <ul class="list-disc space-y-1 pl-5 text-sm" style="color: var(--ink);">
                @foreach ($validationErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @elseif ($isPending)
        {{-- Editable body --}}
        <div class="space-y-3 px-4 py-4">
            @if ($validationErrors !== [])
                <ul class="list-disc space-y-1 rounded-md border px-4 py-2 pl-8 text-sm"
                    style="border-color: var(--state-error); color: var(--state-error); background-color: rgba(220, 38, 38, 0.06);"
                    data-test="tool-call-validation-errors"
                >
                    @foreach ($validationErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <x-dashy.input
                :label="__('Title')"
                wire:model="{{ $editKey }}.title"
                maxlength="200"
                data-test="event-card-title"
            />

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <x-dashy.date-picker
                    name="{{ $editKey }}.start_at"
                    :label="__('Start')"
                    with-time
                    test-id="event-card-start"
                />

                <x-dashy.date-picker
                    name="{{ $editKey }}.end_at"
                    :label="__('End')"
                    with-time
                    test-id="event-card-end"
                />
            </div>

            <label class="flex items-center gap-2">
                <input
                    type="checkbox"
                    wire:model="{{ $editKey }}.is_all_day"
                    class="size-4 rounded border-[var(--border)] bg-[var(--bg-deep)] text-brand-danube focus:ring-brand-danube"
                />
                <span class="text-sm" style="color: var(--ink);">{{ __('All-day event') }}</span>
            </label>

            @if ($colors !== [])
                <div class="space-y-1.5">
                    <span class="text-xs uppercase tracking-wide" style="color: var(--ink-dim);">{{ __('Color') }}</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($colors as $color)
                            <button
                                type="button"
                                wire:click="$set('{{ $editKey }}.color', '{{ $color['value'] }}')"
                                @class([
                                    'size-9 rounded-full border-2 transition focus:outline-none focus:ring-2 focus:ring-brand-danube',
                                    'border-[var(--ink)]' => $selectedColor === $color['value'],
                                    'border-transparent hover:border-[var(--ink-muted)]' => $selectedColor !== $color['value'],
                                ])
                                style="background-color: var({{ $color['var'] }});"
                                aria-label="{{ $color['label'] }}"
                                data-test="event-card-color-{{ $color['value'] }}"
                            ></button>
                        @endforeach
                    </div>
                </div>
            @endif

            <x-dashy.input
                :label="__('Location')"
                wire:model="{{ $editKey }}.location"
                :placeholder="__('Optional')"
                maxlength="200"
            />

            <x-dashy.textarea
                :label="__('Description')"
                wire:model="{{ $editKey }}.description"
                :rows="3"
                maxlength="5000"
            />

            @if ($recurrences !== [])
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <x-dashy.select :label="__('Repeat')" wire:model.live="{{ $editKey }}.recurrence_freq">
                        @foreach ($recurrences as $freq)
                            <option value="{{ $freq['value'] }}">{{ $freq['label'] }}</option>
                        @endforeach
                    </x-dashy.select>

                    @if ($selectedRecurrence !== 'none')
                        <x-dashy.date-picker
                            name="{{ $editKey }}.recurrence_until"
                            :label="__('Repeat until')"
                            test-id="event-card-recurrence-until"
                        />
                    @endif
                </div>
            @endif
        </div>
    @else
        {{-- Read-only body (created / discarded) --}}
        <div class="space-y-3 px-4 py-4">
            @if (! empty($args['title']))
                <div class="font-medium text-[15px]" style="color: var(--ink);">{{ $args['title'] }}</div>
            @endif

            @if (! empty($args['description']))
                <p class="whitespace-pre-wrap text-sm" style="color: var(--ink-muted);">{{ $args['description'] }}</p>
            @endif

            <div class="grid grid-cols-1 gap-x-6 gap-y-2 text-sm md:grid-cols-2">
                @if (! empty($args['start_at']))
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);">{{ __('Start') }}</span>
                        <span style="color: var(--ink);">{{ $args['start_at'] }}</span>
                    </div>
                @endif

                @if (! empty($args['end_at']))
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);">{{ __('End') }}</span>
                        <span style="color: var(--ink);">{{ $args['end_at'] }}</span>
                    </div>
                @endif

                @if (! empty($args['location']))
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);">{{ __('Location') }}</span>
                        <span style="color: var(--ink);">{{ $args['location'] }}</span>
                    </div>
                @endif

                @if (! empty($args['recurrence_freq']) && $args['recurrence_freq'] !== 'none')
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);">{{ __('Repeat') }}</span>
                        <span style="color: var(--ink);">{{ $args['recurrence_freq'] }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Footer / actions --}}
    <div
        class="flex flex-wrap items-center justify-end gap-2 border-t px-4 py-3"
        style="border-color: var(--border);"
    >
        @if ($isPending)
            <button
                type="button"
                wire:click="discardToolCall({{ $message->id }})"
                wire:loading.attr="disabled"
                class="rounded-full border px-4 py-1.5 text-sm transition"
                style="border-color: var(--border-mid); color: var(--ink-muted); background-color: transparent;"
                onmouseover="this.style.color='var(--ink)'; this.style.borderColor='var(--border-strong)';"
                onmouseout="this.style.color='var(--ink-muted)'; this.style.borderColor='var(--border-mid)';"
                data-test="discard-tool-call"
            >
                {{ __('Discard') }}
            </button>
            <button
                type="button"
                wire:click="confirmToolCall({{ $message->id }})"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-medium transition"
                style="background-color: var(--blue); color: white;"
                onmouseover="this.style.opacity='0.9'"
                onmouseout="this.style.opacity='1'"
                data-test="confirm-tool-call"
            >
                <x-dashy.icon name="check" class="size-4" />
                {{ __('Create event') }}
            </button>
        @elseif ($status === 'created')
            <a
                href="{{ route('calendar') }}"
                class="inline-flex items-center gap-1.5 rounded-full border px-4 py-1.5 text-sm transition"
                style="border-color: var(--border-mid); color: var(--ink); background-color: transparent;"
                onmouseover="this.style.borderColor='var(--border-strong)';"
                onmouseout="this.style.borderColor='var(--border-mid)';"
            >
                {{ __('Open in calendar') }}
                <x-dashy.icon name="arrow-up-right" class="size-3.5" />
            </a>
        @elseif ($status === 'discarded')
            <span class="text-xs italic" style="color: var(--ink-dim);">{{ __('No event was created.') }}</span>
        @endif
    </div>
</div>
