@php
    use App\Domains\Tasks\Support\TaskDateFormatter;

    $plainText = $plainText ?? false;

    $isOverdue = $task->end_date !== null
        && $task->end_date->isPast()
        && (!$task->status || !in_array(
            $task->status->category?->value,
            [\App\Domains\Projects\Enums\ProjectStatusCategory::Done->value,
             \App\Domains\Projects\Enums\ProjectStatusCategory::Closed->value], true
        ));

    $startStr = $task->start_date?->toDateString();
    $endStr = $task->end_date?->toDateString();
    $today = now()->toDateString();
    $tomorrow = now()->addDay()->toDateString();
    $nextWeek = now()->addWeek()->toDateString();
    $twoWeeks = now()->addWeeks(2)->toDateString();
    $fourWeeks = now()->addWeeks(4)->toDateString();
    $eightWeeks = now()->addWeeks(8)->toDateString();
@endphp

<x-dashy.dropdown align="end" position="bottom">
    <x-slot:trigger>
        @if ($plainText)
            <button
                type="button"
                wire:click.stop
                class="inline-flex h-5 items-center text-[11px] leading-4 transition focus:outline-none focus-visible:underline hover:underline"
                style="
                    color: {{ $isOverdue ? 'var(--state-error)' : ($task->end_date ? 'var(--ink-muted)' : 'var(--ink-dim)') }};
                "
                data-test="date-trigger-{{ $task->id }}"
            >
                <span>{{ TaskDateFormatter::format($task->end_date) }}</span>
            </button>
        @else
            <button
                type="button"
                wire:click.stop
                class="inline-flex h-5 items-center gap-1 rounded px-1.5 text-[11px] leading-4 transition focus:outline-none focus-visible:ring-2"
                style="
                    color: {{ $isOverdue ? 'var(--state-error)' : ($task->end_date ? 'var(--ink)' : 'var(--ink-dim)') }};
                    --tw-ring-color: var(--blue);
                "
                data-test="date-trigger-{{ $task->id }}"
            >
                <x-dashy.icon name="clock" class="size-3 shrink-0" />
                <span>{{ TaskDateFormatter::format($task->end_date) }}</span>
            </button>
        @endif
    </x-slot:trigger>

    <x-dashy.menu class="!min-w-[280px]">
        <div class="px-2 py-1.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider"
               style="color: var(--ink-dim);">{{ __('Due date') }}</p>
        </div>

        <x-dashy.menu.item as="button" type="button"
            wire:click.stop="updateDates({{ $task->id }}, '{{ $startStr ?? '' }}', '{{ $today }}')"
            data-test="date-quick-today-{{ $task->id }}">
            <span class="text-sm" style="color: var(--ink);">{{ __('Today') }}</span>
        </x-dashy.menu.item>
        <x-dashy.menu.item as="button" type="button"
            wire:click.stop="updateDates({{ $task->id }}, '{{ $startStr ?? '' }}', '{{ $tomorrow }}')"
            data-test="date-quick-tomorrow-{{ $task->id }}">
            <span class="text-sm" style="color: var(--ink);">{{ __('Tomorrow') }}</span>
        </x-dashy.menu.item>
        <x-dashy.menu.item as="button" type="button"
            wire:click.stop="updateDates({{ $task->id }}, '{{ $startStr ?? '' }}', '{{ $nextWeek }}')">
            <span class="text-sm" style="color: var(--ink);">{{ __('Next week') }}</span>
        </x-dashy.menu.item>
        <x-dashy.menu.item as="button" type="button"
            wire:click.stop="updateDates({{ $task->id }}, '{{ $startStr ?? '' }}', '{{ $twoWeeks }}')">
            <span class="text-sm" style="color: var(--ink);">{{ __('In 2 weeks') }}</span>
        </x-dashy.menu.item>
        <x-dashy.menu.item as="button" type="button"
            wire:click.stop="updateDates({{ $task->id }}, '{{ $startStr ?? '' }}', '{{ $fourWeeks }}')">
            <span class="text-sm" style="color: var(--ink);">{{ __('In 4 weeks') }}</span>
        </x-dashy.menu.item>
        <x-dashy.menu.item as="button" type="button"
            wire:click.stop="updateDates({{ $task->id }}, '{{ $startStr ?? '' }}', '{{ $eightWeeks }}')">
            <span class="text-sm" style="color: var(--ink);">{{ __('In 8 weeks') }}</span>
        </x-dashy.menu.item>

        <x-dashy.menu.separator />
        <x-dashy.menu.item
            as="button"
            type="button"
            wire:click="$dispatch('task-detail:open', { taskId: {{ $task->id }} })"
            data-test="date-open-detail-{{ $task->id }}"
        >
            <span class="text-sm" style="color: var(--ink-muted);">{{ __('Pick custom date…') }}</span>
        </x-dashy.menu.item>

        @if ($task->end_date)
            <x-dashy.menu.separator />
            <x-dashy.menu.item as="button" type="button"
                wire:click.stop="updateDates({{ $task->id }}, null, null)"
                data-test="date-clear-{{ $task->id }}">
                <span class="text-sm" style="color: var(--state-error);">{{ __('Clear dates') }}</span>
            </x-dashy.menu.item>
        @endif
    </x-dashy.menu>
</x-dashy.dropdown>
