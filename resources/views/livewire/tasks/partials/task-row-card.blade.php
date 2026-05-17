@php
    use App\Domains\Projects\Enums\ProjectStatusCategory;
    use App\Domains\Projects\Support\ProjectColor;

    /**
     * @var \App\Domains\Tasks\Models\Task $task
     * @var iterable<\App\Models\User> $teamMembers
     * @var \Illuminate\Support\Collection<int, \App\Domains\Projects\Models\ProjectStatus> $allStatuses
     * @var bool $showProjectPill
     * @var bool $showStatusPill
     * @var bool $showCheckbox
     * @var bool $showDragHandle
     * @var bool $plainMeta  When true, project / date / priority render as inline plain text (no badge backgrounds).
     */
    $showProjectPill = $showProjectPill ?? false;
    $showStatusPill = $showStatusPill ?? true;
    $showCheckbox = $showCheckbox ?? true;
    $showDragHandle = $showDragHandle ?? true;
    $plainMeta = $plainMeta ?? false;

    $isComplete = $task->status && in_array(
        $task->status->category,
        [ProjectStatusCategory::Done, ProjectStatusCategory::Closed],
        true
    );

    $projectColorVar = $task->project ? ProjectColor::for($task->project) : '--ink-dim';
@endphp

<div
    wire:key="task-row-{{ $task->id }}"
    data-task-id="{{ $task->id }}"
    data-test="task-row-{{ $task->id }}"
    @class([
        'group flex flex-wrap items-center gap-3 border-b px-4 py-2 transition last:border-b-0',
        'opacity-60' => $task->is_archived,
    ])
    style="border-color: var(--border); background-color: transparent;"
    onmouseover="this.style.backgroundColor='var(--surface-2)'"
    onmouseout="this.style.backgroundColor='transparent'"
>
    {{-- Drag handle (lg+, hover-revealed). --}}
    @if ($showDragHandle)
        <button
            type="button"
            class="task-drag-handle hidden cursor-grab opacity-0 transition group-hover:opacity-100 lg:inline-flex"
            style="color: var(--ink-dim);"
            aria-label="{{ __('Reorder') }}"
            wire:click.stop
        >
            <x-dashy.icon name="bars-3" class="size-3.5" />
        </button>
    @endif

    {{-- Checkbox — wrapped in a 20px box so the 18px checkbox shares the row's
         canonical h-5 cell size and lines up with every other cell. --}}
    @if ($showCheckbox)
        <div class="flex h-5 shrink-0 items-center">
            <button
                type="button"
                wire:click.stop="toggleComplete({{ $task->id }})"
                class="dashy-task-checkbox cursor-pointer"
                aria-checked="{{ $isComplete ? 'true' : 'false' }}"
                role="checkbox"
                aria-label="{{ $isComplete ? __('Mark as not done') : __('Mark as done') }}"
                data-test="task-checkbox-{{ $task->id }}"
            >
                <x-dashy.icon name="check" class="size-3" />
            </button>
        </div>
    @endif

    {{-- Name (and status pill on per-project view). h-5 keeps every cell on
         the same 20px baseline so text centers cleanly across the row. --}}
    <div class="flex h-5 min-w-0 flex-1 items-center gap-2">
        @if ($showStatusPill)
            @include('livewire.tasks.partials.status-popover', [
                'task' => $task,
                'allStatuses' => $allStatuses,
            ])
        @endif

        <button
            type="button"
            wire:click="$dispatch('task-detail:open', { taskId: {{ $task->id }} })"
            @class([
                'min-w-0 flex-1 truncate text-left text-xs leading-5 transition hover:underline',
                'line-through' => $isComplete,
            ])
            style="color: {{ $isComplete ? 'var(--ink-muted)' : 'var(--ink)' }};"
            data-test="task-open-{{ $task->id }}"
        >
            {{ $task->name }}
        </button>

        @if ($task->is_archived)
            <x-dashy.badge data-test="task-row-archived-badge-{{ $task->id }}">
                {{ __('Archived') }}
            </x-dashy.badge>
        @endif
    </div>

    {{-- Fixed-width right columns so project / date / priority line up across rows. --}}
    @if ($showProjectPill)
        <div class="flex h-5 w-32 shrink-0 items-center justify-end">
            @if ($task->project)
                @if ($plainMeta)
                    <a
                        href="{{ route('tasks.show', $task->project) }}"
                        wire:navigate
                        wire:click.stop
                        class="inline-flex h-5 items-center text-[11px] leading-4 transition hover:underline focus-visible:underline focus:outline-none"
                        style="color: var(--ink-muted);"
                        data-test="task-row-project-{{ $task->id }}"
                        title="{{ __('Open :name', ['name' => $task->project->name]) }}"
                    >
                        <span class="max-w-[8rem] truncate">{{ $task->project->name }}</span>
                    </a>
                @else
                    <a
                        href="{{ route('tasks.show', $task->project) }}"
                        wire:navigate
                        wire:click.stop
                        class="inline-flex h-5 items-center gap-1 rounded px-1.5 text-[11px] leading-4 transition"
                        style="
                            background-color: color-mix(in srgb, var({{ $projectColorVar }}) 14%, transparent);
                            color: color-mix(in srgb, var({{ $projectColorVar }}) 75%, var(--ink));
                        "
                        data-test="task-row-project-{{ $task->id }}"
                        title="{{ __('Open :name', ['name' => $task->project->name]) }}"
                    >
                        <span class="inline-block size-1 rounded-full" style="background-color: var({{ $projectColorVar }});"></span>
                        <span class="max-w-[7rem] truncate">{{ $task->project->name }}</span>
                    </a>
                @endif
            @endif
        </div>
    @endif

    <div class="flex h-5 w-20 shrink-0 items-center justify-end">
        @include('livewire.tasks.partials.date-popover', ['task' => $task, 'plainText' => $plainMeta])
    </div>

    <div class="flex h-5 w-16 shrink-0 items-center justify-end">
        @include('livewire.tasks.partials.priority-popover', ['task' => $task, 'plainText' => $plainMeta])
    </div>

    {{-- Actions menu (lg+, hover-revealed) --}}
    <div class="shrink-0">
        <x-dashy.popover align="end" position="bottom">
            <x-slot:trigger>
                <button
                    type="button"
                    :class="open ? '!opacity-100' : ''"
                    class="inline-flex size-7 items-center justify-center rounded-md opacity-100 transition lg:opacity-0 lg:group-hover:opacity-100 focus-visible:opacity-100 focus:outline-none focus-visible:ring-2"
                    style="color: var(--ink-muted); --tw-ring-color: var(--blue);"
                    onmouseover="this.style.color='var(--ink)'; this.style.backgroundColor='rgba(var(--ink-rgb), 0.06)';"
                    onmouseout="this.style.color='var(--ink-muted)'; this.style.backgroundColor='transparent';"
                    aria-label="{{ __('Task actions') }}"
                    data-test="task-row-actions-{{ $task->id }}"
                >
                    <x-dashy.icon name="ellipsis-horizontal" class="size-4" />
                </button>
            </x-slot:trigger>

            <x-dashy.menu>
                @if ($task->is_archived)
                    <x-dashy.menu.item
                        icon="archive-box-x-mark"
                        wire:click="unarchiveTask({{ $task->id }})"
                        data-test="task-row-unarchive-{{ $task->id }}"
                    >
                        {{ __('Unarchive') }}
                    </x-dashy.menu.item>
                @else
                    <x-dashy.menu.item
                        icon="archive-box-arrow-down"
                        wire:click="archiveTask({{ $task->id }})"
                        data-test="task-row-archive-{{ $task->id }}"
                    >
                        {{ __('Archive') }}
                    </x-dashy.menu.item>
                @endif

                <x-dashy.menu.item
                    icon="trash"
                    wire:click="deleteTask({{ $task->id }})"
                    data-test="task-row-delete-{{ $task->id }}"
                >
                    {{ __('Delete') }}
                </x-dashy.menu.item>
            </x-dashy.menu>
        </x-dashy.popover>
    </div>
</div>
