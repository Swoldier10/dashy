@php
    $colorVar = $status->category->colorVar();
    $count = $tasks->count();
    $hasTasks = $count > 0;
@endphp

<section
    class="flex flex-col gap-2"
    data-test="status-group-{{ $status->id }}"
>
    <header class="flex items-center justify-between gap-2 px-1 py-1">
        <button
            type="button"
            wire:click="toggleStatusCollapse({{ $status->id }})"
            class="flex items-center gap-2 rounded-md px-1 py-1 transition focus:outline-none focus-visible:ring-2"
            style="--tw-ring-color: var(--blue);"
            aria-expanded="{{ $isCollapsed ? 'false' : 'true' }}"
            data-test="status-group-toggle-{{ $status->id }}"
        >
            <x-dashy.icon
                :name="$isCollapsed ? 'chevron-right' : 'chevron-down'"
                class="size-3.5 shrink-0"
                style="color: var(--ink-dim);"
            />
            <span
                class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-medium"
                style="background-color: color-mix(in srgb, var({{ $colorVar }}) 14%, transparent); color: color-mix(in srgb, var({{ $colorVar }}) 80%, var(--ink));"
            >
                <span class="inline-block size-1.5 rounded-full" style="background-color: var({{ $colorVar }});"></span>
                <span>{{ $status->name }}</span>
            </span>
            <span class="text-xs" style="color: var(--ink-dim);">{{ $count }}</span>
        </button>

        <button
            type="button"
            wire:click="openCreateTask({{ $status->id }})"
            class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs transition"
            style="color: var(--ink-dim);"
            onmouseover="this.style.color='var(--blue)'; this.style.backgroundColor='color-mix(in srgb, var(--blue) 10%, transparent)';"
            onmouseout="this.style.color='var(--ink-dim)'; this.style.backgroundColor='transparent';"
            data-test="status-add-task-{{ $status->id }}"
        >
            <x-dashy.icon name="plus" class="size-3.5" />
            <span>{{ __('Add') }}</span>
        </button>
    </header>

    @if (! $isCollapsed)
        <div
            x-data
            x-init="
                if (window.Sortable) {
                    new window.Sortable($el, {
                        animation: 150,
                        group: 'tasks-{{ $projectId }}',
                        handle: '.task-drag-handle',
                        draggable: '[data-task-id]',
                        onEnd: (evt) => {
                            const taskId = parseInt(evt.item.dataset.taskId, 10);
                            const fromStatus = parseInt(evt.from.dataset.statusId, 10);
                            const toStatus = parseInt(evt.to.dataset.statusId, 10);
                            const sourceIds = [...evt.from.children].map(el => parseInt(el.dataset.taskId, 10)).filter(Number.isInteger);
                            const targetIds = [...evt.to.children].map(el => parseInt(el.dataset.taskId, 10)).filter(Number.isInteger);
                            if (fromStatus === toStatus) {
                                $wire.reorderTasks(toStatus, targetIds);
                            } else {
                                $wire.moveTask(taskId, toStatus, sourceIds, targetIds);
                            }
                        },
                    });
                }
            "
            wire:ignore.self
            data-status-id="{{ $status->id }}"
            data-test="status-sortable-{{ $status->id }}"
            @class([
                'flex flex-col overflow-hidden',
                'rounded-xl' => $hasTasks,
            ])
            style="
                min-height: {{ $hasTasks ? '0' : '8px' }};
                background-color: {{ $hasTasks ? 'var(--surface)' : 'transparent' }};
                box-shadow: {{ $hasTasks
                    ? '0 1px 2px rgba(var(--ink-rgb), 0.04), 0 0 0 1px var(--border) inset'
                    : 'none' }};
            "
        >
            @foreach ($tasks as $task)
                @include('livewire.tasks.partials.task-row', [
                    'task' => $task,
                    'teamMembers' => $teamMembers,
                    'allStatuses' => $allStatuses,
                ])
            @endforeach
        </div>
    @endif
</section>
