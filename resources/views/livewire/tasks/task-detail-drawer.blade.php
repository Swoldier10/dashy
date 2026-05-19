@php
    use App\Domains\Tasks\Enums\TaskPriority;
    $priorities = [TaskPriority::Urgent, TaskPriority::High, TaskPriority::Normal, TaskPriority::Low];
    $priorityOptions = [];
    foreach ($priorities as $priorityOption) {
        $priorityOptions[$priorityOption->value] = $priorityOption->label();
    }
    $statusOptions = $this->statuses->mapWithKeys(fn ($s) => [(string) $s->id => $s->name])->all();
    $task = $this->task;
    $colorVar = $task?->status?->category?->colorVar() ?? '--ink-dim';
    $timePanelTaskId = $task?->id ?? 0;
@endphp

<div>
    {{-- Drawer is closed purely client-side via Alpine ($store.modals.close).
         No wire:close handler — that would force a server roundtrip and run
         Livewire's morph over this drawer's content, which can crash when
         traversing complex @if blocks. --}}
    <x-dashy.drawer
        name="task-detail"
        side="right"
        size="lg"
        class="md:!max-w-[820px]"
        focusable
    >
        <div wire:key="task-detail-content-{{ $taskId ?? 'empty' }}" class="flex h-full min-h-0 flex-col">
            <form
                wire:submit.prevent
                class="flex h-full min-h-0 flex-col"
                data-test="task-detail-{{ $task?->id ?? 'empty' }}"
            >
                {{-- Header bar: badge + project. Shrink-0 keeps it pinned to the
                     top of the drawer regardless of body length. --}}
                @if ($task)
                    <header
                        class="flex shrink-0 items-center justify-between gap-3 border-b px-6 py-5"
                        style="border-color: var(--border);"
                    >
                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider"
                                style="background-color: color-mix(in srgb, var({{ $colorVar }}) 18%, transparent); color: var({{ $colorVar }});"
                            >
                                <x-dashy.icon name="pencil-square" class="size-3" />
                                <span>{{ __('Edit task') }}</span>
                            </span>
                            <span class="truncate text-xs" style="color: var(--ink-dim);">{{ $task->project->name }}</span>
                        </div>
                    </header>
                @endif

                {{-- Scrollable body styled as a property grid (ClickUp-style).
                     - flex-1 + min-h-0 + overflow-y-auto: middle section absorbs
                       remaining vertical space and scrolls; header/footer pin.
                     - grid grid-cols-[160px_1fr] on ≥sm: label column / value column.
                       Title, description, and attachments span both columns.
                     - The <livewire:task-time-panel> mount stays OUTSIDE @if($task)
                       (Livewire 4 morph workaround — see TaskTimePanel::mount).
                       That panel renders its own label/value pair as direct grid
                       children via display:contents on its <section>. --}}
                <div
                    class="task-detail-form grid min-h-0 flex-1 grid-cols-1 content-start gap-x-4 gap-y-1 overflow-y-auto px-6 py-6 sm:grid-cols-[160px_minmax(0,1fr)]"
                >
                    @if ($task)
                        {{-- Title: spans both columns, chromeless big heading --}}
                        <div class="mb-2 sm:col-span-2">
                            <input
                                type="text"
                                wire:model.blur="detailName"
                                wire:change="saveTaskDetail"
                                maxlength="200"
                                placeholder="{{ __('What needs to be done?') }}"
                                data-test="task-detail-name"
                                class="task-detail-title-input w-full rounded-md border bg-transparent px-3 py-2 font-display text-2xl font-semibold leading-tight outline-none focus:outline-none"
                                style="color: var(--ink); border-color: var(--border-mid);"
                            />
                        </div>

                        {{-- Status row --}}
                        <div class="task-row-label flex items-center gap-2 py-1.5 text-[13px]" style="color: var(--ink-muted);">
                            <x-dashy.icon name="check-badge" class="size-3.5" />
                            <span>{{ __('Status') }}</span>
                        </div>
                        <div class="task-row-value flex min-w-0 items-center py-0.5">
                            <x-dashy.searchable-select
                                wire:model.live="detailStatusId"
                                :options="$statusOptions"
                                :placeholder="__('Select a status')"
                                :searchPlaceholder="__('Search statuses…')"
                                :emptyMessage="__('No statuses match your search.')"
                                data-test="task-detail-status"
                            />
                        </div>

                        {{-- Assignees row: selected strip + Alpine dropdown picker --}}
                        <div class="task-row-label flex items-center gap-2 py-1.5 text-[13px]" style="color: var(--ink-muted);">
                            <x-dashy.icon name="user" class="size-3.5" />
                            <span>{{ __('Assignees') }}</span>
                        </div>
                        <div
                            class="task-row-value flex min-w-0 flex-wrap items-center gap-1.5 py-0.5"
                            x-data="{ open: false }"
                            @click.outside="open = false"
                            @keydown.escape.window="open = false"
                        >
                            @foreach ($task->assignees as $assignee)
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[12px]"
                                    style="background-color: var(--surface-2); color: var(--ink);"
                                >
                                    <x-dashy.avatar
                                        :name="$assignee->name"
                                        :initials="$assignee->initials()"
                                        :src="$assignee->avatar"
                                        size="xs"
                                    />
                                    <span class="truncate">{{ $assignee->name }}</span>
                                </span>
                            @endforeach
                            @if ($this->teamMembers->isNotEmpty())
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click="open = ! open"
                                        class="inline-flex size-6 items-center justify-center rounded-full transition hover:bg-[var(--surface-2)]"
                                        style="color: var(--ink-muted);"
                                        aria-label="{{ __('Edit assignees') }}"
                                    >
                                        <x-dashy.icon name="plus" class="size-3.5" />
                                    </button>
                                    <div
                                        x-show="open"
                                        x-cloak
                                        x-transition.opacity.duration.120ms
                                        class="absolute left-0 top-full z-50 mt-1 w-64 rounded-lg border p-1 shadow-lg"
                                        style="background: var(--surface); border-color: var(--border);"
                                    >
                                        @foreach ($this->teamMembers as $member)
                                            @php $isSelected = $task->assignees->contains('id', $member->id); @endphp
                                            <button
                                                type="button"
                                                wire:click="toggleAssignee({{ $member->id }})"
                                                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm transition hover:bg-[var(--surface-2)]"
                                                style="color: var(--ink);"
                                                data-test="task-detail-assignee-{{ $member->id }}"
                                            >
                                                <x-dashy.avatar
                                                    :name="$member->name"
                                                    :initials="$member->initials()"
                                                    :src="$member->avatar"
                                                    size="xs"
                                                />
                                                <span class="flex-1 truncate">{{ $member->name }}</span>
                                                @if ($isSelected)
                                                    <x-dashy.icon name="check" class="size-3.5 shrink-0" style="color: var(--blue);" />
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Dates row: start → due, inline --}}
                        <div class="task-row-label flex items-center gap-2 py-1.5 text-[13px]" style="color: var(--ink-muted);">
                            <x-dashy.icon name="calendar-days" class="size-3.5" />
                            <span>{{ __('Dates') }}</span>
                        </div>
                        <div class="task-row-value flex min-w-0 flex-wrap items-center gap-1.5 py-0.5">
                            <x-dashy.date-picker
                                name="detailStartDate"
                                :placeholder="__('Start')"
                                on-change="saveTaskDetail"
                                with-time
                                test-id="task-detail-start-date"
                            />
                            <span style="color: var(--ink-dim);">→</span>
                            <x-dashy.date-picker
                                name="detailEndDate"
                                :placeholder="__('Due')"
                                on-change="saveTaskDetail"
                                with-time
                                test-id="task-detail-end-date"
                            />
                        </div>

                        {{-- Priority row --}}
                        <div class="task-row-label flex items-center gap-2 py-1.5 text-[13px]" style="color: var(--ink-muted);">
                            <x-dashy.icon name="flag" class="size-3.5" />
                            <span>{{ __('Priority') }}</span>
                        </div>
                        <div class="task-row-value flex min-w-0 items-center py-0.5">
                            <x-dashy.searchable-select
                                wire:model.live="detailPriority"
                                :options="$priorityOptions"
                                :placeholder="__('Select a priority')"
                                :searchPlaceholder="__('Search priorities…')"
                                :emptyMessage="__('No priorities match your search.')"
                                data-test="task-detail-priority"
                            />
                        </div>
                    @elseif ($taskId !== null)
                        <p class="sm:col-span-2" style="color: var(--ink-muted);">
                            {{ __('This task is no longer available.') }}
                        </p>
                    @endif

                    {{-- Track time row: mount stays outside @if($task) (morph workaround).
                         The panel's <section> uses display:contents so its emitted
                         label/value pair lines up with the property grid above. --}}
                    <livewire:time-tracking.task-time-panel
                        :taskId="$timePanelTaskId"
                        :key="'time-panel-'.$timePanelTaskId"
                    />

                    @if ($task)
                        {{-- Beschreibung section --}}
                        <div class="task-description mt-4 sm:col-span-2">
                            <p
                                class="mb-2 text-[11px] font-semibold uppercase tracking-wider"
                                style="color: var(--ink-dim);"
                            >
                                {{ __('Beschreibung') }}
                            </p>
                            <x-dashy.textarea
                                wire:model.blur="detailDescription"
                                wire:change="saveTaskDetail"
                                rows="14"
                                maxlength="5000"
                                data-test="task-detail-description"
                            />
                        </div>

                        {{-- Attachments --}}
                        @if (! empty($task->attachments))
                            <div class="sm:col-span-2">
                                <p
                                    class="mb-2 text-[11px] font-semibold uppercase tracking-wider"
                                    style="color: var(--ink-dim);"
                                >
                                    {{ __('Attachments') }}
                                </p>
                                <div class="flex flex-wrap gap-2" data-test="task-detail-attachments">
                                    @foreach ($task->attachments as $att)
                                        @if (($att['type'] ?? null) === 'image' && ! empty($att['url']))
                                            <a
                                                href="{{ $att['url'] }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="block overflow-hidden rounded-lg border"
                                                style="border-color: var(--border-mid);"
                                            >
                                                <img
                                                    src="{{ $att['url'] }}"
                                                    alt="{{ $att['name'] ?? '' }}"
                                                    class="block h-24 w-24 object-cover sm:h-28 sm:w-28"
                                                    loading="lazy"
                                                />
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Footer pinned to bottom: created-by meta + destructive action. --}}
                @if ($task)
                    <footer
                        class="flex shrink-0 items-center justify-between gap-2 border-t px-6 py-4"
                        style="border-color: var(--border);"
                    >
                        <p class="text-xs" style="color: var(--ink-dim);">
                            @if ($task->creator)
                                {{ __('Created by :name', ['name' => $task->creator->name]) }}
                                ·
                            @endif
                            {{ $task->created_at?->diffForHumans() }}
                        </p>
                        <x-dashy.button
                            type="button"
                            variant="danger"
                            icon="trash"
                            size="sm"
                            wire:click="deleteTask"
                            data-test="task-detail-delete"
                        >
                            {{ __('Delete') }}
                        </x-dashy.button>
                    </footer>
                @endif
            </form>
        </div>
    </x-dashy.drawer>
</div>
