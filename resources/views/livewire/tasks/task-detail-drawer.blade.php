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

                {{-- Scrollable body. flex-1 + min-h-0 + overflow-y-auto so the
                     middle section absorbs all remaining vertical space and
                     scrolls internally, leaving the header/footer fixed. --}}
                <div class="flex min-h-0 flex-1 flex-col gap-6 overflow-y-auto px-6 py-6">
                    @if ($task)
                        {{-- Title --}}
                        <x-dashy.input
                            wire:model.blur="detailName"
                            wire:change="saveTaskDetail"
                            :label="__('Name')"
                            :placeholder="__('What needs to be done?')"
                            maxlength="200"
                            data-test="task-detail-name"
                        />

                        {{-- Status / Priority / Start / Due --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-dashy.searchable-select
                                wire:model.live="detailStatusId"
                                :label="__('Status')"
                                :options="$statusOptions"
                                :placeholder="__('Select a status')"
                                :searchPlaceholder="__('Search statuses…')"
                                :emptyMessage="__('No statuses match your search.')"
                                data-test="task-detail-status"
                            />

                            <x-dashy.searchable-select
                                wire:model.live="detailPriority"
                                :label="__('Priority')"
                                :options="$priorityOptions"
                                :placeholder="__('Select a priority')"
                                :searchPlaceholder="__('Search priorities…')"
                                :emptyMessage="__('No priorities match your search.')"
                                data-test="task-detail-priority"
                            />

                            <x-dashy.date-picker
                                name="detailStartDate"
                                :label="__('Start date')"
                                :placeholder="__('Start date')"
                                on-change="saveTaskDetail"
                                with-time
                                test-id="task-detail-start-date"
                            />

                            <x-dashy.date-picker
                                name="detailEndDate"
                                :label="__('Due date')"
                                :placeholder="__('Due date')"
                                on-change="saveTaskDetail"
                                with-time
                                test-id="task-detail-end-date"
                            />
                        </div>

                        {{-- Assignees --}}
                        <div>
                            <x-dashy.label>{{ __('Assignees') }}</x-dashy.label>
                            @if ($this->teamMembers->isEmpty())
                                <p class="text-sm" style="color: var(--ink-dim);">{{ __('No team members.') }}</p>
                            @else
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($this->teamMembers as $member)
                                        @php $isSelected = $task->assignees->contains('id', $member->id); @endphp
                                        <button
                                            type="button"
                                            wire:click="toggleAssignee({{ $member->id }})"
                                            class="flex items-center gap-2 rounded-full border px-2 py-1 text-sm transition"
                                            style="
                                                border-color: {{ $isSelected ? 'var(--blue)' : 'var(--border-strong)' }};
                                                background-color: {{ $isSelected ? 'color-mix(in srgb, var(--blue) 15%, transparent)' : 'transparent' }};
                                                color: var(--ink);
                                            "
                                            data-test="task-detail-assignee-{{ $member->id }}"
                                        >
                                            <x-dashy.avatar
                                                :name="$member->name"
                                                :initials="$member->initials()"
                                                :src="$member->avatar"
                                                size="xs"
                                            />
                                            <span class="truncate">{{ $member->name }}</span>
                                            @if ($isSelected)
                                                <x-dashy.icon name="check" class="size-3.5 shrink-0" style="color: var(--blue);" />
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Description --}}
                        <x-dashy.textarea
                            wire:model.blur="detailDescription"
                            wire:change="saveTaskDetail"
                            :label="__('Description')"
                            rows="6"
                            maxlength="5000"
                            data-test="task-detail-description"
                        />

                        {{-- Attachments --}}
                        @if (! empty($task->attachments))
                            <div>
                                <x-dashy.label>{{ __('Attachments') }}</x-dashy.label>
                                <div class="mt-1.5 flex flex-wrap gap-2" data-test="task-detail-attachments">
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
                    @elseif ($taskId !== null)
                        <p style="color: var(--ink-muted);">
                            {{ __('This task is no longer available.') }}
                        </p>
                    @endif

                    {{-- Time tracking lives OUTSIDE the @if($task) block and is
                         the LAST child of the body so it always renders in the
                         DOM (taskId=0 sidestep — see TaskTimePanel::mount).
                         Livewire 4's morph engine corrupts BLOCK comment
                         tracking when a <livewire:…> child sits inside an
                         @if/@else, so it must stay outside the conditional. --}}
                    <livewire:time-tracking.task-time-panel
                        :taskId="$timePanelTaskId"
                        :key="'time-panel-'.$timePanelTaskId"
                    />
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
