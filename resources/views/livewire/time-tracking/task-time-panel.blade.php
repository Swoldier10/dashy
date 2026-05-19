@php
    use App\Domains\TimeTracking\Support\DurationParser;
    $total = $this->totalSeconds;
    $running = $this->runningEntry;
    $runningHere = $this->isRunningForCurrentUser;
    $entries = $this->entries;
@endphp

<section
    wire:key="task-time-panel-{{ $taskId }}"
    data-test="task-time-panel"
    @class([
        'rounded-xl border p-4' => $taskId !== 0,
    ])
    @style([
        'background-color: var(--surface-2); border-color: var(--border);' => $taskId !== 0,
    ])
>
    {{-- $taskId === 0 renders a stable empty root so this component can stay
         mounted as a sibling (rather than a conditional child) of the
         task-detail drawer's content. See TaskTimePanel::mount() for context. --}}
    @if ($taskId !== 0)
    <div class="flex items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-2">
            <x-dashy.icon name="clock" class="size-4 shrink-0" style="color: var(--ink-muted);" />
            <x-dashy.label class="!mb-0">{{ __('Track time') }}</x-dashy.label>
        </div>

        <div class="flex items-center gap-3">
            <span
                @if ($running)
                    x-data="{ base: {{ $total }}, startedAt: '{{ $running->started_at?->toIso8601String() }}', startedFromNow: {{ $running ? max(0, (int) now()->diffInSeconds($running->started_at, true)) : 0 }}, value: 0, init() { this.tick(); this._t = setInterval(() => this.tick(), 1000); }, tick() { const delta = Math.max(0, Math.floor((Date.now() - new Date(this.startedAt).getTime()) / 1000)); this.value = this.base - this.startedFromNow + delta; }, fmt() { const s = Math.max(0, this.value); const h = Math.floor(s / 3600); const m = Math.floor((s % 3600) / 60); if (h > 0 && m > 0) return h + 'h ' + m + 'm'; if (h > 0) return h + 'h'; if (m > 0) return m + 'm'; return (s % 60) + 's'; } }"
                    x-text="fmt()"
                @endif
                class="text-sm tabular-nums"
                style="color: var(--ink);"
                data-test="task-time-total"
            >
                {{ DurationParser::format($total) }}
            </span>

            <x-dashy.dropdown align="end" position="bottom" :closeOnClickInside="false" panelClass="w-[360px] sm:w-[420px]">
                <x-slot:trigger>
                    <x-dashy.button
                        variant="{{ $runningHere ? 'danger' : 'filled' }}"
                        size="sm"
                        :icon="$runningHere ? 'stop' : 'play'"
                        data-test="task-time-popover-trigger"
                    >
                        {{ $runningHere ? __('Stop') : __('Track time') }}
                    </x-dashy.button>
                </x-slot:trigger>

                <div class="flex flex-col gap-4 p-4" data-test="task-time-popover">
                    <header class="flex items-center justify-between gap-2 border-b pb-3" style="border-color: var(--border);">
                        <div class="flex flex-col">
                            <span class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--ink-dim);">
                                {{ __('Time on this task') }}
                            </span>
                            <span class="text-base font-semibold" style="color: var(--ink);">
                                {{ DurationParser::format($total) }}
                            </span>
                        </div>
                        @if ($runningHere)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider"
                                style="background-color: color-mix(in srgb, var(--state-success) 18%, transparent); color: var(--state-success);"
                            >
                                <span class="inline-block size-1.5 rounded-full" style="background-color: var(--state-success);"></span>
                                {{ __('Running') }}
                            </span>
                        @endif
                    </header>

                    @if ($runningHere && $running)
                        <div class="flex items-center justify-between gap-3 rounded-lg border px-3 py-3"
                             style="border-color: var(--border-mid); background-color: var(--bg-deep);">
                            <div
                                x-data="{ startedAt: '{{ $running->started_at?->toIso8601String() }}', value: 0, init() { this.tick(); this._t = setInterval(() => this.tick(), 1000); }, tick() { this.value = Math.max(0, Math.floor((Date.now() - new Date(this.startedAt).getTime()) / 1000)); } }"
                            >
                                <span
                                    class="font-mono text-2xl tabular-nums"
                                    style="color: var(--ink);"
                                    x-text="(function(s){const h=Math.floor(s/3600),m=Math.floor((s%3600)/60),x=s%60;return String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(x).padStart(2,'0');})(value)"
                                    data-test="task-time-running-clock"
                                >00:00:00</span>
                            </div>
                            <x-dashy.button
                                variant="danger"
                                size="sm"
                                icon="stop"
                                wire:click="stopTimer"
                                data-test="task-time-stop"
                            >
                                {{ __('Stop') }}
                            </x-dashy.button>
                        </div>
                    @else
                        <form wire:submit.prevent="logManual" class="flex flex-col gap-3">
                            <div class="flex items-stretch gap-2">
                                <div class="flex-1">
                                    <x-dashy.input
                                        wire:model="manualDuration"
                                        @keydown.enter.prevent="$wire.logManual()"
                                        :placeholder="__('Enter time (ex: 3h 20m) or start timer')"
                                        maxlength="32"
                                        :showError="false"
                                        data-test="task-time-manual-input"
                                    />
                                </div>
                                <x-dashy.button
                                    x-show="!(($wire.manualDuration ?? '').trim())"
                                    type="button"
                                    variant="primary"
                                    size="md"
                                    icon="play"
                                    wire:click="startTimer"
                                    data-test="task-time-start"
                                >
                                    {{ __('Start') }}
                                </x-dashy.button>
                                <x-dashy.button
                                    x-show="!!(($wire.manualDuration ?? '').trim())"
                                    x-cloak
                                    type="submit"
                                    variant="primary"
                                    size="md"
                                    icon="check"
                                    data-test="task-time-save-manual"
                                >
                                    {{ __('Save') }}
                                </x-dashy.button>
                            </div>

                            <x-dashy.field-error name="duration" />

                            <div x-show="!!(($wire.manualDuration ?? '').trim())" x-cloak>
                                <x-dashy.textarea
                                    wire:model.blur="manualNotes"
                                    :label="__('Notes')"
                                    rows="2"
                                    maxlength="2000"
                                    :showError="false"
                                    data-test="task-time-manual-notes"
                                />
                            </div>
                        </form>
                    @endif

                    {{-- Entries list --}}
                    <div class="flex flex-col gap-2" data-test="task-time-entries">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--ink-dim);">
                                {{ __('Time entries') }}
                            </span>
                            <span class="text-[11px]" style="color: var(--ink-dim);">{{ $entries->count() }}</span>
                        </div>

                        {{-- Keep the <ul> always rendered so Livewire's morph never
                             has to flip <p> ↔ <ul> at this slot. Toggling sibling
                             tags inside a deeply-nested @if/@else trips Livewire 4's
                             Block tracker ("Cannot read properties of null (reading
                             'before')") and corrupts the DOM patch. --}}
                        <ul class="flex flex-col divide-y" style="border-color: var(--border);">
                            @forelse ($entries as $entry)
                                <li
                                    wire:key="task-time-entry-{{ $entry->id }}"
                                    class="py-2"
                                    data-test="task-time-entry-{{ $entry->id }}"
                                    style="border-color: var(--border);"
                                >
                                        @if ($editingEntryId === $entry->id)
                                            <div class="flex flex-col gap-2">
                                                <div class="flex items-center gap-2">
                                                    <x-dashy.input
                                                        wire:model="editDuration"
                                                        :placeholder="__('Duration (ex: 1h 15m)')"
                                                        :showError="false"
                                                        data-test="task-time-entry-edit-duration-{{ $entry->id }}"
                                                    />
                                                </div>
                                                <x-dashy.textarea
                                                    wire:model="editNotes"
                                                    rows="2"
                                                    :placeholder="__('Notes')"
                                                    :showError="false"
                                                    data-test="task-time-entry-edit-notes-{{ $entry->id }}"
                                                />
                                                <x-dashy.field-error name="duration" />
                                                <div class="flex items-center justify-end gap-2">
                                                    <x-dashy.button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        wire:click="cancelEditing"
                                                        data-test="task-time-entry-edit-cancel-{{ $entry->id }}"
                                                    >
                                                        {{ __('Cancel') }}
                                                    </x-dashy.button>
                                                    <x-dashy.button
                                                        type="button"
                                                        variant="primary"
                                                        size="sm"
                                                        icon="check"
                                                        wire:click="saveEntry({{ $entry->id }})"
                                                        data-test="task-time-entry-edit-save-{{ $entry->id }}"
                                                    >
                                                        {{ __('Save') }}
                                                    </x-dashy.button>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-start gap-2">
                                                <x-dashy.avatar
                                                    :name="$entry->user?->name ?? ''"
                                                    :initials="$entry->user?->initials() ?? '?'"
                                                    :src="$entry->user?->avatar"
                                                    size="xs"
                                                />
                                                <div class="flex min-w-0 flex-1 flex-col">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-medium tabular-nums" style="color: var(--ink);">
                                                            @if ($entry->isRunning())
                                                                <span style="color: var(--state-success);">{{ __('Running…') }}</span>
                                                            @else
                                                                {{ DurationParser::format((int) $entry->duration_seconds) }}
                                                            @endif
                                                        </span>
                                                        <span class="text-xs" style="color: var(--ink-dim);">
                                                            {{ $entry->started_at?->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                    @if (! empty($entry->notes))
                                                        <span class="truncate text-xs" style="color: var(--ink-muted);" title="{{ $entry->notes }}">
                                                            {{ $entry->notes }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @unless ($entry->isRunning())
                                                    <x-dashy.popover align="end" position="bottom">
                                                        <x-slot:trigger>
                                                            <button
                                                                type="button"
                                                                class="inline-flex size-7 items-center justify-center rounded-md transition focus:outline-none focus-visible:ring-2"
                                                                style="color: var(--ink-muted); --tw-ring-color: var(--blue);"
                                                                aria-label="{{ __('Entry actions') }}"
                                                                data-test="task-time-entry-actions-{{ $entry->id }}"
                                                            >
                                                                <x-dashy.icon name="ellipsis-horizontal" class="size-4" />
                                                            </button>
                                                        </x-slot:trigger>
                                                        <x-dashy.menu>
                                                            <x-dashy.menu.item
                                                                icon="pencil-square"
                                                                wire:click="startEditing({{ $entry->id }})"
                                                                data-test="task-time-entry-edit-{{ $entry->id }}"
                                                            >
                                                                {{ __('Edit') }}
                                                            </x-dashy.menu.item>
                                                            <x-dashy.menu.item
                                                                icon="trash"
                                                                wire:click="deleteEntry({{ $entry->id }})"
                                                                data-test="task-time-entry-delete-{{ $entry->id }}"
                                                            >
                                                                {{ __('Delete') }}
                                                            </x-dashy.menu.item>
                                                        </x-dashy.menu>
                                                    </x-dashy.popover>
                                                @endunless
                                            </div>
                                    @endif
                                </li>
                            @empty
                                <li
                                    wire:key="task-time-entries-empty"
                                    class="py-2 text-sm"
                                    style="color: var(--ink-dim);"
                                    data-test="task-time-entries-empty"
                                >
                                    {{ __('No time logged yet.') }}
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </x-dashy.dropdown>
        </div>
    </div>
    @endif
</section>
