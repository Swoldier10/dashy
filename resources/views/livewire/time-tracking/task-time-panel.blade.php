@php
    use App\Domains\TimeTracking\Support\DurationParser;
    use Illuminate\Support\Facades\Auth;

    $total = $this->totalSeconds;
    $running = $this->runningEntry;
    $runningHere = $this->isRunningForCurrentUser;
    $entries = $this->entries;
    $entriesByUser = $this->entriesByUser;
    $authUser = Auth::user();
@endphp

<section
    wire:key="task-time-panel-{{ $taskId }}"
    data-test="task-time-panel"
    @class([
        'contents' => $taskId !== 0,
    ])
>
    {{-- $taskId === 0 renders a stable empty root so this component can stay
         mounted as a sibling (rather than a conditional child) of the
         task-detail drawer's content. See TaskTimePanel::mount() for context.

         When populated, `display: contents` on the section makes its two
         children (label cell + value cell) participate directly in the
         parent task-detail property grid — no teleport, no relocation of
         the Livewire mount. --}}
    @if ($taskId !== 0)
        {{-- Track time row label --}}
        <div class="task-row-label flex items-center gap-2 py-1.5 text-[13px]" style="color: var(--ink-muted);">
            <x-dashy.icon name="clock" class="size-3.5 shrink-0" />
            <span>{{ __('Track time') }}</span>
        </div>

        {{-- Track time row value: chromeless trigger + absolute-positioned panel.
             Uses the same positioning approach as `<x-dashy.date-picker>`
             (position: relative wrapper + position: absolute panel) instead
             of dashy.dropdown's position: fixed — because the drawer card's
             slide-in animation leaves a transform residue that would promote
             the drawer to be the containing block for any position:fixed
             descendant (and let the drawer's overflow-y:auto clip it).
             Absolute positioning is unaffected by transform ancestors. --}}
        <div
            class="task-row-value relative flex min-w-0 items-center py-0.5"
            x-data="{ open: false }"
            @click.outside="open = false"
            @keydown.escape.window="open && (open = false)"
        >
            {{-- Chromeless inline trigger (idle = no bg, hover = surface-2;
                 running = soft state-error tint). Matches ClickUp's compact
                 `▶ 26m` look. --}}
            <button
                type="button"
                @click="open = ! open"
                @class([
                    'inline-flex items-center gap-1.5 rounded-md px-1.5 py-0.5 text-[13px] font-medium transition focus:outline-none focus-visible:ring-2',
                    'is-running' => $runningHere,
                ])
                @style([
                    'background-color: color-mix(in srgb, var(--state-error) 14%, transparent); color: var(--state-error);' => $runningHere,
                    'color: var(--ink);' => ! $runningHere,
                    '--tw-ring-color: var(--blue);',
                ])
                onmouseover="if (!this.classList.contains('is-running')) this.style.backgroundColor = 'var(--surface-2)';"
                onmouseout="if (!this.classList.contains('is-running')) this.style.backgroundColor = '';"
                data-test="task-time-popover-trigger"
                aria-label="{{ $runningHere ? __('Stop') : __('Track time') }}"
                :aria-expanded="open ? 'true' : 'false'"
            >
                <x-dashy.icon :name="$runningHere ? 'stop' : 'play'" variant="solid" class="size-3.5" />
                <span
                    @if ($running)
                        x-data="{ base: {{ $total }}, startedAt: '{{ $running->started_at?->toIso8601String() }}', startedFromNow: {{ $running ? max(0, (int) now()->diffInSeconds($running->started_at, true)) : 0 }}, value: 0, init() { this.tick(); this._t = setInterval(() => this.tick(), 1000); }, tick() { const delta = Math.max(0, Math.floor((Date.now() - new Date(this.startedAt).getTime()) / 1000)); this.value = this.base - this.startedFromNow + delta; }, fmt() { const s = Math.max(0, this.value); const h = Math.floor(s / 3600); const m = Math.floor((s % 3600) / 60); if (h > 0 && m > 0) return h + 'h ' + m + 'm'; if (h > 0) return h + 'h'; if (m > 0) return m + 'm'; return (s % 60) + 's'; } }"
                        x-text="fmt()"
                    @endif
                    class="tabular-nums"
                >
                    {{ DurationParser::format($total) }}
                </span>
            </button>

            {{-- Popover panel: five vertical sections separated by thin borders.
                 Matches ClickUp's track-time popover layout. Positioned
                 absolute below the trigger so it scrolls with the drawer
                 body and is not subject to the drawer's transform-CB. --}}
            <div
                x-show="open"
                x-cloak
                x-transition.opacity.duration.120ms
                class="absolute left-0 top-[calc(100%+8px)] z-50 flex w-[min(360px,calc(100vw-2rem))] flex-col overflow-hidden rounded-xl border sm:w-[400px]"
                style="background: var(--surface); border-color: var(--border); box-shadow: 0 24px 60px -20px rgba(var(--ink-rgb), 0.28), 0 4px 12px -4px rgba(var(--ink-rgb), 0.12);"
                data-test="task-time-popover">

                    {{-- 1. Header: TIME ON THIS TASK + total --}}
                    <header
                        class="flex items-center justify-between gap-2 border-b px-5 py-4"
                        style="border-color: var(--border);"
                    >
                        <span
                            class="text-[11px] font-semibold uppercase tracking-wider"
                            style="color: var(--ink-dim);"
                        >
                            {{ __('Time on this task') }}
                        </span>
                        <span
                            class="text-lg font-semibold tabular-nums"
                            style="color: var(--ink);"
                            data-test="task-time-total"
                        >
                            {{ DurationParser::format($total) }}
                        </span>
                    </header>

                    {{-- 2. Assignee chip: static, current user (no selector — we
                         only track for the current user per Auth scope). --}}
                    @if ($authUser)
                        <div
                            class="flex items-center gap-2.5 border-b px-5 py-3"
                            style="border-color: var(--border);"
                        >
                            <x-dashy.avatar
                                :name="$authUser->name"
                                :initials="$authUser->initials()"
                                :src="$authUser->avatar"
                                size="xs"
                            />
                            <span class="text-sm font-medium" style="color: var(--ink);">{{ $authUser->name }}</span>
                        </div>
                    @endif

                    {{-- 3. Running clock band (only when timer running for current user) --}}
                    @if ($runningHere && $running)
                        <div
                            class="flex items-center justify-between gap-3 border-b px-5 py-4"
                            style="border-color: var(--border); background-color: var(--bg-deep);"
                        >
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
                    @endif

                    {{-- 4. Manual entry form: input + single morphing CTA + always-visible notes.
                         Uses raw <input>/<textarea> elements (not <x-dashy.input>/<x-dashy.textarea>)
                         so we get a lighter ClickUp-style chrome — chromeless notes, thin
                         border on duration input — without fighting the global .dashy-input
                         border weight. wire:model attributes pass through identically. --}}
                    @unless ($runningHere && $running)
                        <form
                            wire:submit.prevent="logManual"
                            class="flex flex-col gap-3 border-b px-5 py-4"
                            style="border-color: var(--border);"
                        >
                            {{-- Duration input + single morphing CTA --}}
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    wire:model="manualDuration"
                                    @keydown.enter.prevent="$wire.logManual()"
                                    placeholder="{{ __('Enter time (ex: 3h 20m) or start timer') }}"
                                    maxlength="32"
                                    class="task-time-input w-full min-w-0 rounded-md border bg-transparent px-3 py-2 text-sm outline-none transition focus:outline-none"
                                    style="border-color: var(--border-mid); color: var(--ink);"
                                    data-test="task-time-manual-input"
                                />

                                {{-- Single circular CTA — morphs between Start (input empty)
                                     and Save (input filled). Both buttons keep their original
                                     data-test ids so existing/future DOM-based tests resolve. --}}
                                <button
                                    type="button"
                                    x-show="!(($wire.manualDuration ?? '').trim())"
                                    wire:click="startTimer"
                                    class="inline-flex size-9 shrink-0 items-center justify-center rounded-full transition focus:outline-none focus-visible:ring-2 hover:opacity-90"
                                    style="background-color: var(--blue); color: var(--surface); --tw-ring-color: var(--blue);"
                                    data-test="task-time-start"
                                    aria-label="{{ __('Start timer') }}"
                                >
                                    <x-dashy.icon name="play" variant="solid" class="size-4" />
                                </button>
                                <button
                                    type="submit"
                                    x-show="!!(($wire.manualDuration ?? '').trim())"
                                    x-cloak
                                    class="inline-flex size-9 shrink-0 items-center justify-center rounded-full transition focus:outline-none focus-visible:ring-2 hover:opacity-90"
                                    style="background-color: var(--blue); color: var(--surface); --tw-ring-color: var(--blue);"
                                    data-test="task-time-save-manual"
                                    aria-label="{{ __('Save entry') }}"
                                >
                                    <x-dashy.icon name="check" variant="solid" class="size-4" />
                                </button>
                            </div>

                            <x-dashy.field-error name="duration" />

                            {{-- Notes: fully chromeless, no focus-within visual transition,
                                 so the row looks identical whether focused or idle. Layout
                                 stays stable regardless of input state. --}}
                            <div class="task-time-notes-row flex items-start gap-2.5 px-3 py-1">
                                <x-dashy.icon
                                    name="document-text"
                                    class="mt-0.5 size-4 shrink-0"
                                    style="color: var(--ink-muted);"
                                />
                                <textarea
                                    wire:model.blur="manualNotes"
                                    rows="1"
                                    placeholder="{{ __('Notes') }}"
                                    maxlength="2000"
                                    class="task-time-notes w-full resize-none border-0 bg-transparent p-0 text-sm leading-tight outline-none focus:outline-none focus:ring-0"
                                    style="color: var(--ink);"
                                    data-test="task-time-manual-notes"
                                ></textarea>
                            </div>
                        </form>
                    @endunless

                    {{-- 5. Entries list grouped by user, expandable.
                         Keep the outer <ul> always rendered so Livewire's morph
                         never has to flip <p> ↔ <ul> at this slot — toggling
                         sibling tags inside a deeply-nested @if/@else trips
                         Livewire 4's Block tracker. --}}
                    <div class="flex flex-col px-5 py-4" data-test="task-time-entries">
                        <div class="mb-3 flex items-center justify-between">
                            <span
                                class="text-[11px] font-semibold uppercase tracking-wider"
                                style="color: var(--ink-dim);"
                            >
                                {{ __('Time entries') }}
                            </span>
                            @if ($entries->count() > 0)
                                <span
                                    class="inline-flex min-w-[20px] items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-semibold tabular-nums"
                                    style="background-color: var(--surface-2); color: var(--ink-muted);"
                                >
                                    {{ $entries->count() }}
                                </span>
                            @endif
                        </div>

                        <ul class="flex flex-col gap-0.5">
                            @forelse ($entriesByUser as $group)
                                <li
                                    wire:key="task-time-group-{{ $group['user']?->id ?? 0 }}"
                                    x-data="{ open: true }"
                                    class="flex flex-col"
                                >
                                    {{-- Group header: chevron + avatar + name + total --}}
                                    <button
                                        type="button"
                                        @click="open = ! open"
                                        class="flex items-center gap-2.5 rounded-md px-1.5 py-2 text-left transition hover:bg-[var(--surface-2)] focus:outline-none focus-visible:ring-2"
                                        style="--tw-ring-color: var(--blue);"
                                        :aria-expanded="open ? 'true' : 'false'"
                                    >
                                        <x-dashy.icon
                                            name="chevron-right"
                                            class="size-3.5 shrink-0 transition-transform"
                                            x-bind:class="open ? 'rotate-90' : ''"
                                            style="color: var(--ink-muted);"
                                        />
                                        <x-dashy.avatar
                                            :name="$group['user']?->name ?? '?'"
                                            :initials="$group['user']?->initials() ?? '?'"
                                            :src="$group['user']?->avatar"
                                            size="xs"
                                        />
                                        <span class="flex-1 truncate text-sm font-medium" style="color: var(--ink);">
                                            {{ $group['user']?->name ?? __('Unknown') }}
                                        </span>
                                        <span class="text-sm font-semibold tabular-nums" style="color: var(--ink);">
                                            {{ DurationParser::format($group['total_seconds']) }}
                                        </span>
                                    </button>

                                    {{-- Expanded entry rows --}}
                                    <ul
                                        x-show="open"
                                        x-cloak
                                        class="ml-7 flex flex-col"
                                    >
                                        @foreach ($group['entries'] as $entry)
                                            <li
                                                wire:key="task-time-entry-{{ $entry->id }}"
                                                class="border-t py-2.5 first:border-t-0"
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
                                        @endforeach
                                    </ul>
                                </li>
                            @empty
                                <li
                                    wire:key="task-time-entries-empty"
                                    class="flex items-center justify-center rounded-md py-6 text-sm"
                                    style="color: var(--ink-dim); background-color: var(--surface-2);"
                                    data-test="task-time-entries-empty"
                                >
                                    {{ __('No time logged yet.') }}
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
        </div>
    @endif
</section>
