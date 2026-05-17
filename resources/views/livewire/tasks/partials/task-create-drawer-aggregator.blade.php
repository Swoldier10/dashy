@php
    use App\Domains\Tasks\Enums\TaskPriority;
    $priorities = [TaskPriority::Urgent, TaskPriority::High, TaskPriority::Normal, TaskPriority::Low];
    $priorityOptions = [];
    foreach ($priorities as $priorityOption) {
        $priorityOptions[$priorityOption->value] = $priorityOption->label();
    }
    $projectOptions = $this->projects->mapWithKeys(fn ($p) => [(string) $p->id => $p->name])->all();
    $statusOptions = $this->createProjectStatuses->mapWithKeys(fn ($s) => [(string) $s->id => $s->name])->all();
    $createMembers = $this->createProjectMembers;
@endphp

<x-dashy.drawer
    name="task-create"
    side="right"
    size="lg"
    class="md:!max-w-[820px]"
    focusable
    wire:close="closeCreateTask"
>
    <form wire:submit.prevent="submitCreateTask" class="flex flex-col gap-6 p-6" data-test="task-create-drawer">
        <div class="flex items-start justify-between gap-3">
            <span
                class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider"
                style="background-color: color-mix(in srgb, var(--blue) 18%, transparent); color: var(--blue);"
            >
                <x-dashy.icon name="plus" class="size-3" />
                <span>{{ __('New task') }}</span>
            </span>
        </div>

        <x-dashy.input
            wire:model="createName"
            :label="__('Name')"
            :placeholder="__('What needs to be done?')"
            maxlength="200"
            required
            data-test="task-create-name"
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-dashy.searchable-select
                    wire:model.live="createProjectId"
                    :label="__('Project')"
                    :options="$projectOptions"
                    :placeholder="__('Pick a project')"
                    :searchPlaceholder="__('Search projects…')"
                    :emptyMessage="__('No projects available.')"
                    data-test="task-create-project"
                />
            </div>

            <div>
                <x-dashy.searchable-select
                    wire:model="createStatusId"
                    :label="__('Status')"
                    :options="$statusOptions"
                    :placeholder="__('Select a status')"
                    :searchPlaceholder="__('Search statuses…')"
                    :emptyMessage="__('Pick a project first.')"
                    data-test="task-create-status"
                />
            </div>

            <div>
                <x-dashy.searchable-select
                    wire:model="createPriority"
                    :label="__('Priority')"
                    :options="$priorityOptions"
                    :placeholder="__('Select a priority')"
                    :searchPlaceholder="__('Search priorities…')"
                    :emptyMessage="__('No priorities match your search.')"
                    data-test="task-create-priority"
                />
            </div>

            <div>
                <x-dashy.date-picker
                    name="createStartDate"
                    :label="__('Start date')"
                    :placeholder="__('Start date')"
                    test-id="task-create-start-date"
                />
            </div>

            <div>
                <x-dashy.date-picker
                    name="createEndDate"
                    :label="__('Due date')"
                    :placeholder="__('Due date')"
                    test-id="task-create-end-date"
                />
            </div>
        </div>

        <div>
            <x-dashy.label>{{ __('Assignees') }}</x-dashy.label>
            @if ($createMembers->isEmpty())
                <p class="mt-1.5 text-sm" style="color: var(--ink-dim);">{{ __('Pick a project to see members.') }}</p>
            @else
                <div class="mt-1.5 flex flex-wrap gap-2">
                    @foreach ($createMembers as $member)
                        @php $isSelected = in_array($member->id, $createAssigneeIds, true); @endphp
                        <button
                            type="button"
                            wire:click="toggleCreateAssignee({{ $member->id }})"
                            class="flex items-center gap-2 rounded-full border px-2 py-1 text-sm transition"
                            style="
                                border-color: {{ $isSelected ? 'var(--blue)' : 'var(--border-strong)' }};
                                background-color: {{ $isSelected ? 'color-mix(in srgb, var(--blue) 15%, transparent)' : 'transparent' }};
                                color: var(--ink);
                            "
                            data-test="task-create-assignee-{{ $member->id }}"
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

        <x-dashy.textarea
            wire:model="createDescription"
            :label="__('Description')"
            rows="6"
            maxlength="5000"
            data-test="task-create-description"
        />

        <div class="flex items-center justify-end gap-2 border-t pt-4" style="border-color: var(--border);">
            <x-dashy.modal.close>
                <x-dashy.button type="button" variant="filled">{{ __('Cancel') }}</x-dashy.button>
            </x-dashy.modal.close>
            <x-dashy.button
                type="submit"
                variant="primary"
                icon="plus"
                data-test="task-create-submit"
            >
                {{ __('Create task') }}
            </x-dashy.button>
        </div>
    </form>
</x-dashy.drawer>
