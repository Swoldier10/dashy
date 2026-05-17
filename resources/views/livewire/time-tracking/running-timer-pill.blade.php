@php
    $active = $this->activeEntry;
@endphp

<div wire:key="running-timer-pill">
    @if ($active && $active->task && $active->task->project)
        <div
            class="fixed z-40 bottom-4 right-4 sm:top-4 sm:bottom-auto lg:top-4 lg:right-6"
            data-test="running-timer-pill"
        >
            <div
                class="flex items-center gap-2 rounded-full border px-3 py-1.5 shadow-lg backdrop-blur"
                style="
                    background-color: color-mix(in srgb, var(--surface) 88%, transparent);
                    border-color: var(--border-strong);
                    color: var(--ink);
                "
            >
                <span
                    class="inline-flex size-2 shrink-0 rounded-full"
                    style="background-color: var(--state-success);"
                    aria-hidden="true"
                ></span>

                <a
                    href="{{ route('tasks.show', $active->task->project_id) }}?task={{ $active->task_id }}"
                    class="flex min-w-0 items-center gap-2 text-sm transition hover:underline"
                    style="color: var(--ink);"
                    wire:navigate
                    data-test="running-timer-pill-link"
                >
                    <x-dashy.icon name="play" class="size-3.5 shrink-0" style="color: var(--state-success);" />
                    <span class="max-w-[160px] truncate sm:max-w-[220px]" title="{{ $active->task->name }}">
                        {{ $active->task->name }}
                    </span>
                    <span
                        class="font-mono text-xs tabular-nums"
                        style="color: var(--ink-muted);"
                        x-data="{ startedAt: '{{ $active->started_at?->toIso8601String() }}', value: 0, init() { this.tick(); this._t = setInterval(() => this.tick(), 1000); }, tick() { this.value = Math.max(0, Math.floor((Date.now() - new Date(this.startedAt).getTime()) / 1000)); } }"
                        x-text="(function(s){const h=Math.floor(s/3600),m=Math.floor((s%3600)/60),x=s%60;return String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(x).padStart(2,'0');})(value)"
                        data-test="running-timer-pill-clock"
                    >00:00:00</span>
                </a>

                <button
                    type="button"
                    wire:click="stop"
                    aria-label="{{ __('Stop timer') }}"
                    class="inline-flex size-7 items-center justify-center rounded-full transition focus:outline-none focus-visible:ring-2"
                    style="color: var(--ink-muted); --tw-ring-color: var(--blue);"
                    onmouseover="this.style.color='var(--state-error)'; this.style.backgroundColor='color-mix(in srgb, var(--state-error) 12%, transparent)';"
                    onmouseout="this.style.color='var(--ink-muted)'; this.style.backgroundColor='transparent';"
                    data-test="running-timer-pill-stop"
                >
                    <x-dashy.icon name="stop" class="size-3.5" />
                </button>
            </div>
        </div>
    @endif
</div>
