<?php

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\FindProjectService;
use App\Domains\Projects\Services\ListProjectsForUserService;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ArchiveTaskService;
use App\Domains\Tasks\Services\AssignTaskService;
use App\Domains\Tasks\Services\BulkArchiveTasksService;
use App\Domains\Tasks\Services\BulkDeleteTasksService;
use App\Domains\Tasks\Services\BulkMoveTasksService;
use App\Domains\Tasks\Services\BulkUpdateTaskDueDateService;
use App\Domains\Tasks\Services\BulkUpdateTaskPriorityService;
use App\Domains\Tasks\Services\CreateTaskService;
use App\Domains\Tasks\Services\DeleteTaskService;
use App\Domains\Tasks\Services\FindTaskService;
use App\Domains\Tasks\Services\ListAllTasksForUserService;
use App\Domains\Tasks\Services\ListTasksForProjectService;
use App\Domains\Tasks\Services\MoveTaskService;
use App\Domains\Tasks\Services\ReorderTasksService;
use App\Domains\Tasks\Services\TaskExistsInProjectService;
use App\Domains\Tasks\Services\UnarchiveTaskService;
use App\Domains\Tasks\Services\UnassignTaskService;
use App\Domains\Tasks\Services\UpdateTaskDatesService;
use App\Domains\Tasks\Services\UpdateTaskPriorityService;
use App\Domains\Tasks\Services\UpdateTaskStatusService;
use App\Domains\Teams\Services\ListTeamsForUserService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Tasks')] class extends Component
{
    use DispatchesDashyUi;

    public int $projectId;

    #[Url(as: 'tab', keep: false)]
    public string $activeTab = 'tasks';

    #[Url(as: 'archived', keep: false)]
    public bool $showArchived = false;

    /** Source context — when 'everything', keeps the Everything chip active in the top bar. */
    #[Url(as: 'from', keep: false)]
    public ?string $fromContext = null;

    /** @var array<int,int> */
    public array $collapsedStatusIds = [];

    /** Task ids selected via the row checkboxes for bulk actions. @var array<int,int> */
    public array $selectedTaskIds = [];

    /** Bound by the bulk toolbar's custom due-date picker. */
    public ?string $bulkDueDateInput = null;

    // Create-task drawer form state
    public bool $createOpen = false;

    public ?int $createStatusId = null;

    public string $createName = '';

    public string $createDescription = '';

    public string $createPriority = 'normal';

    public ?string $createStartDate = null;

    public ?string $createEndDate = null;

    /** @var array<int,int> */
    public array $createAssigneeIds = [];

    public ?int $initialTaskId = null;

    public function mount(int $project, FindProjectService $findProject): void
    {
        $resolved = $findProject->execute(Auth::user(), $project);
        Gate::authorize('viewAny', [Task::class, $resolved]);
        $this->projectId = $resolved->id;

        if (! in_array($this->activeTab, ['tasks', 'dashboard'], true)) {
            $this->activeTab = 'tasks';
        }

        $requestedTaskId = request()->query('task');
        if (is_numeric($requestedTaskId)) {
            $taskId = (int) $requestedTaskId;
            if (app(TaskExistsInProjectService::class)->execute($taskId, $this->projectId)) {
                // Hydrate the child drawer on the initial render. Dispatching
                // `task-detail:open` from mount() doesn't reach the child on
                // first paint — the event fires post-render. Passing the id as
                // a mount prop is the supported way.
                $this->initialTaskId = $taskId;
            }
        }
    }

    #[Computed]
    public function project(): Project
    {
        return app(FindProjectService::class)->execute(Auth::user(), $this->projectId);
    }

    /** @return Collection<int, \App\Domains\Projects\Models\ProjectStatus> */
    #[Computed]
    public function statuses(): Collection
    {
        // Reverse-enum order so the most-advanced statuses show first
        // (closed → done → active → not started). The relation already orders
        // by position within a category, and sortByDesc is stable so that
        // position order is preserved.
        $rank = array_flip(array_column(ProjectStatusCategory::cases(), 'value'));

        return $this->project->statuses
            ->sortByDesc(fn ($s) => $rank[$s->category->value] ?? -1)
            ->values();
    }

    /** @return Collection<int, \App\Models\User> */
    #[Computed]
    public function teamMembers(): Collection
    {
        return $this->project->team->members()->get();
    }

    /** @return array<int, Collection<int, Task>> */
    #[Computed]
    public function tasksByStatusId(): array
    {
        return app(ListTasksForProjectService::class)
            ->execute(Auth::user(), $this->project, $this->showArchived)
            ->groupBy('project_status_id')
            ->all();
    }

    /** @return Collection<int, \App\Domains\Teams\Models\Team> */
    #[Computed]
    public function teams(): Collection
    {
        return app(ListTeamsForUserService::class)->execute(Auth::user());
    }

    /** @return Collection<int, Project> */
    #[Computed]
    public function userProjects(): Collection
    {
        return app(ListProjectsForUserService::class)->execute(Auth::user());
    }

    /** @return array<int, \Illuminate\Support\Collection<int, Project>> */
    #[Computed]
    public function projectsByTeamId(): array
    {
        return $this->userProjects->groupBy('team_id')->all();
    }

    /** @return Collection<int, Task> */
    #[Computed]
    public function allTasksAcrossTeams(): Collection
    {
        return app(ListAllTasksForUserService::class)
            ->execute(Auth::user(), null, $this->showArchived);
    }

    /** @return array<int, int> */
    #[Computed]
    public function teamCounts(): array
    {
        return $this->allTasksAcrossTeams
            ->groupBy(fn (Task $t) => $t->project->team_id)
            ->map->count()
            ->all();
    }

    /** @return array<int, int> */
    #[Computed]
    public function projectTaskCounts(): array
    {
        return $this->allTasksAcrossTeams
            ->groupBy('project_id')
            ->map->count()
            ->all();
    }

    public function toggleTaskSelection(int $taskId): void
    {
        $idx = array_search($taskId, $this->selectedTaskIds, true);

        if ($idx === false) {
            $this->selectedTaskIds[] = $taskId;

            return;
        }

        unset($this->selectedTaskIds[$idx]);
        $this->selectedTaskIds = array_values($this->selectedTaskIds);
    }

    public function clearSelection(): void
    {
        $this->selectedTaskIds = [];
    }

    /** Whether every currently visible task is in the bulk selection. */
    #[Computed]
    public function allVisibleSelected(): bool
    {
        $visible = $this->visibleTaskIds();

        return $visible !== [] && array_diff($visible, $this->selectedTaskIds) === [];
    }

    public function toggleSelectAll(): void
    {
        $visible = $this->visibleTaskIds();

        if ($visible === []) {
            return;
        }

        if (array_diff($visible, $this->selectedTaskIds) === []) {
            $this->clearSelection();

            return;
        }

        $this->selectedTaskIds = $visible;
    }

    /** @return list<int> */
    private function visibleTaskIds(): array
    {
        return collect($this->tasksByStatusId)
            ->flatMap(fn ($tasks) => $tasks->pluck('id')->all())
            ->all();
    }

    /**
     * Selection filtered to tasks currently visible on the page, so stale
     * or forged ids never reach the bulk services.
     *
     * @return list<int>
     */
    private function selectedVisibleIds(): array
    {
        return array_values(array_intersect($this->selectedTaskIds, $this->visibleTaskIds()));
    }

    public function bulkSetStatus(int $statusId, BulkMoveTasksService $svc): void
    {
        $ids = $this->selectedVisibleIds();
        if ($ids === []) {
            return;
        }

        $svc->execute(Auth::user(), $ids, $statusId);

        $this->clearSelection();
        $this->toast('success', __('Status updated.'));
        $this->dispatch('task-list-changed');
    }

    public function bulkSetPriority(string $priority, BulkUpdateTaskPriorityService $svc): void
    {
        $ids = $this->selectedVisibleIds();
        if ($ids === []) {
            return;
        }

        $svc->execute(Auth::user(), $ids, $priority);

        $this->clearSelection();
        $this->toast('success', __('Priority updated.'));
        $this->dispatch('task-list-changed');
    }

    public function bulkSetDueDate(?string $dueDate, BulkUpdateTaskDueDateService $svc): void
    {
        $ids = $this->selectedVisibleIds();
        if ($ids === []) {
            return;
        }

        $svc->execute(Auth::user(), $ids, $dueDate);

        $this->clearSelection();
        $this->toast('success', $dueDate === null ? __('Dates cleared.') : __('Due date updated.'));
        $this->dispatch('task-list-changed');
    }

    public function applyBulkCustomDueDate(BulkUpdateTaskDueDateService $svc): void
    {
        if ($this->bulkDueDateInput === null || $this->bulkDueDateInput === '') {
            return;
        }

        $this->bulkSetDueDate($this->bulkDueDateInput, $svc);
        $this->bulkDueDateInput = null;
    }

    public function bulkArchive(BulkArchiveTasksService $svc): void
    {
        $ids = $this->selectedVisibleIds();
        if ($ids === []) {
            return;
        }

        $svc->execute(Auth::user(), $ids);

        $this->clearSelection();
        $this->toast('success', __('Tasks archived.'));
        $this->dispatch('task-list-changed');
    }

    public function confirmBulkDelete(): void
    {
        if ($this->selectedVisibleIds() === []) {
            return;
        }

        $this->openModal('confirm-bulk-delete');
    }

    public function bulkDelete(BulkDeleteTasksService $svc): void
    {
        $ids = $this->selectedVisibleIds();
        if ($ids === []) {
            $this->closeModal('confirm-bulk-delete');

            return;
        }

        $svc->execute(Auth::user(), $ids);

        $this->clearSelection();
        $this->closeModal('confirm-bulk-delete');
        $this->toast('success', __('Tasks deleted.'));
        $this->dispatch('task-list-changed');
    }

    public function openCreateProjectModal(): void
    {
        $this->dispatch('open-create-project');
    }

    public function openCreateTask(?int $statusId = null): void
    {
        $this->resetCreateForm();
        $this->createStatusId = $statusId ?? $this->statuses->first()?->id;
        $this->createOpen = true;

        $this->openModal('task-create');
    }

    public function closeCreateTask(): void
    {
        $this->resetCreateForm();
        $this->createOpen = false;
    }

    public function toggleCreateAssignee(int $userId): void
    {
        $idx = array_search($userId, $this->createAssigneeIds, true);

        if ($idx === false) {
            $this->createAssigneeIds[] = $userId;

            return;
        }

        unset($this->createAssigneeIds[$idx]);
        $this->createAssigneeIds = array_values($this->createAssigneeIds);
    }

    public function submitCreateTask(CreateTaskService $create, AssignTaskService $assign): void
    {
        if ($this->createStatusId === null) {
            return;
        }

        $task = $create->execute(Auth::user(), $this->project, [
            'name' => $this->createName,
            'project_status_id' => $this->createStatusId,
            'description' => $this->createDescription !== '' ? $this->createDescription : null,
            'priority' => $this->createPriority,
            'start_date' => $this->createStartDate ?: null,
            'end_date' => $this->createEndDate ?: null,
        ]);

        foreach ($this->createAssigneeIds as $userId) {
            $assign->execute(Auth::user(), $task->id, (int) $userId);
        }

        $this->resetCreateForm();
        $this->createOpen = false;

        $this->closeModal('task-create');
        $this->toast('success', __('Task created.'));
        $this->dispatch('task-list-changed');
    }

    private function resetCreateForm(): void
    {
        $this->createStatusId = null;
        $this->createName = '';
        $this->createDescription = '';
        $this->createPriority = TaskPriority::Normal->value;
        $this->createStartDate = null;
        $this->createEndDate = null;
        $this->createAssigneeIds = [];
        $this->resetErrorBag();
    }

    public function updateStatus(int $taskId, int $statusId, UpdateTaskStatusService $svc): void
    {
        $svc->execute(Auth::user(), $taskId, $statusId);
        $this->dispatch('task-list-changed');
    }

    public function updatePriority(int $taskId, string $priority, UpdateTaskPriorityService $svc): void
    {
        $svc->execute(Auth::user(), $taskId, $priority);
        $this->dispatch('task-list-changed');
    }

    public function updateDates(int $taskId, ?string $start, ?string $end, UpdateTaskDatesService $svc): void
    {
        $svc->execute(Auth::user(), $taskId, $start, $end);
        $this->dispatch('task-list-changed');
    }

    public function toggleAssignee(int $taskId, int $userId, AssignTaskService $assign, UnassignTaskService $unassign): void
    {
        $task = app(FindTaskService::class)->execute(Auth::user(), $taskId);

        if ($task->assignees->contains('id', $userId)) {
            $unassign->execute(Auth::user(), $taskId, $userId);
        } else {
            $assign->execute(Auth::user(), $taskId, $userId);
        }

        $this->dispatch('task-list-changed');
    }

    /** @param  list<int|string>  $taskIds */
    public function reorderTasks(int $statusId, array $taskIds, ReorderTasksService $svc): void
    {
        $svc->execute(Auth::user(), $statusId, $taskIds);
        $this->dispatch('task-list-changed');
    }

    /**
     * @param  list<int|string>  $sourceIds
     * @param  list<int|string>  $targetIds
     */
    public function moveTask(int $taskId, int $toStatusId, array $sourceIds, array $targetIds, MoveTaskService $svc): void
    {
        $svc->execute(Auth::user(), $taskId, $toStatusId, $sourceIds, $targetIds);
        $this->dispatch('task-list-changed');
    }

    public function openTaskDetail(int $taskId): void
    {
        // Thin passthrough — the <livewire:tasks.task-detail-drawer /> child
        // listens for this event and owns the rest of the flow.
        $this->dispatch('task-detail:open', taskId: $taskId);
    }

    public function deleteTask(int $taskId, DeleteTaskService $svc): void
    {
        // Row-menu delete (task-row.blade.php's actions menu). The drawer's
        // own delete lives on the child component now.
        $svc->execute(Auth::user(), $taskId);

        $this->toast('success', __('Task deleted.'));
        $this->dispatch('task-list-changed');
    }

    public function toggleArchivedVisibility(): void
    {
        $this->showArchived = ! $this->showArchived;
    }

    /**
     * Keep "selection ⊆ visible tasks" as a render-time invariant: selected
     * ids that left the visible list this request (deleted, archived out,
     * hidden by the archived toggle) leave the selection before the toolbar
     * count paints. A lifecycle hook — self-dispatched events like
     * task-list-changed only reach listeners on the *next* browser round
     * trip, so an event-driven prune would lag one request behind.
     */
    public function rendering(mixed $view, mixed $data): void
    {
        if ($this->selectedTaskIds === []) {
            return;
        }

        unset($this->tasksByStatusId); // Bust the computed memo — visibility may have changed this request.
        $this->selectedTaskIds = array_values(array_intersect($this->selectedTaskIds, $this->visibleTaskIds()));
    }

    public function archiveTask(int $taskId, ArchiveTaskService $svc): void
    {
        $svc->execute(Auth::user(), $taskId);

        $this->toast('success', __('Task archived.'));
        $this->dispatch('task-list-changed');
    }

    public function unarchiveTask(int $taskId, UnarchiveTaskService $svc): void
    {
        $svc->execute(Auth::user(), $taskId);

        $this->toast('success', __('Task unarchived.'));
        $this->dispatch('task-list-changed');
    }

    public function toggleStatusCollapse(int $statusId): void
    {
        $idx = array_search($statusId, $this->collapsedStatusIds, true);

        if ($idx === false) {
            $this->collapsedStatusIds[] = $statusId;

            return;
        }

        unset($this->collapsedStatusIds[$idx]);
        $this->collapsedStatusIds = array_values($this->collapsedStatusIds);
    }

    #[On('task-list-changed')]
    public function refresh(): void
    {
        // Empty body — re-renders so computeds re-evaluate. The rendering()
        // hook prunes the bulk selection on every pass.
    }

    #[On('time-entries-updated')]
    public function refreshTaskTotals(): void
    {
        // Empty body — re-renders so the task list totals refresh.
    }
}; ?>

<div class="flex min-h-0 flex-1 flex-col" data-test="tasks-page">
    @include('livewire.tasks.partials.page-top-bar', [
        'teams' => $this->teams,
        'teamCounts' => $this->teamCounts,
        'totalCount' => $this->allTasksAcrossTeams->count(),
        'activeTeamId' => $this->fromContext === 'everything' ? null : $this->project->team_id,
        'everythingHref' => route('tasks'),
    ])

    <div class="mx-auto flex w-full max-w-screen-2xl flex-1 min-h-0 flex-col gap-4 overflow-y-auto px-4 py-4 lg:flex-row lg:gap-6 lg:overflow-hidden lg:px-6 lg:py-0">
        @include('livewire.tasks.partials.workspace-sidebar', [
            'teams' => $this->teams,
            'projectsByTeamId' => $this->projectsByTeamId,
            'projectTaskCounts' => $this->projectTaskCounts,
            'totalCount' => $this->allTasksAcrossTeams->count(),
            'activeProjectId' => $this->project->id,
            'isEverythingActive' => false,
            'activeTeam' => $this->fromContext === 'everything' ? null : $this->project->team,
            'activeTeamCount' => (int) ($this->teamCounts[$this->project->team_id] ?? 0),
        ])

        <div class="flex min-w-0 flex-1 flex-col gap-4 lg:h-full lg:overflow-y-auto lg:py-4">
            @include('livewire.tasks.partials.page-heading', [
                'breadcrumb' => [
                    ['label' => __('Workspace'), 'href' => route('tasks')],
                    ['label' => $this->project->team->name, 'href' => route('tasks', ['team' => $this->project->team_id])],
                    ['label' => $this->project->name, 'href' => null],
                ],
                'project' => $this->project,
                'title' => $this->project->name,
                'showArchived' => $this->showArchived,
                'settingsTeamId' => $this->project->team_id,
            ])

            {{-- Tasks / Dashboard segmented switch (preserved from the prior tab UI). --}}
            <div class="flex items-center" data-test="tasks-action-bar">
                <x-dashy.tabs wire-model="activeTab" default-value="{{ $activeTab }}">
                    <x-dashy.tab value="tasks">{{ __('Tasks') }}</x-dashy.tab>
                    <x-dashy.tab value="dashboard">{{ __('Dashboard') }}</x-dashy.tab>
                </x-dashy.tabs>
            </div>

            <div @class([
                'flex flex-col gap-3',
                'hidden' => $activeTab !== 'tasks',
            ])>
                {{-- Global select-all for bulk actions (hidden when the list is empty). --}}
                @if ($this->tasksByStatusId !== [])
                    @php $allSelected = $this->allVisibleSelected; @endphp
                    <button
                        type="button"
                        wire:click="toggleSelectAll"
                        class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 self-start px-1 text-xs transition md:min-h-0 md:py-1 focus:outline-none focus-visible:ring-2"
                        style="color: var(--ink-muted); --tw-ring-color: var(--blue);"
                        onmouseover="this.style.color='var(--ink)';"
                        onmouseout="this.style.color='var(--ink-muted)';"
                        role="checkbox"
                        aria-checked="{{ $allSelected ? 'true' : 'false' }}"
                        aria-label="{{ $allSelected ? __('Deselect all tasks') : __('Select all tasks') }}"
                        data-test="select-all-tasks"
                    >
                        <span class="dashy-task-checkbox" aria-checked="{{ $allSelected ? 'true' : 'false' }}" aria-hidden="true">
                            <x-dashy.icon name="check" class="size-3" />
                        </span>
                        <span>{{ __('Select all') }}</span>
                    </button>
                @endif

                @forelse ($this->statuses as $status)
                    @include('livewire.tasks.partials.status-group', [
                        'status' => $status,
                        'tasks' => $this->tasksByStatusId[$status->id] ?? collect(),
                        'isCollapsed' => in_array($status->id, $this->collapsedStatusIds, true),
                        'projectId' => $this->project->id,
                        'teamMembers' => $this->teamMembers,
                        'allStatuses' => $this->statuses,
                        'selectedTaskIds' => $selectedTaskIds,
                    ])
                @empty
                    @include('livewire.tasks.partials.no-statuses-empty-state', ['project' => $this->project])
                @endforelse
            </div>

            <div @class([
                'flex-1',
                'hidden' => $activeTab !== 'dashboard',
            ])>
                <livewire:projects.project-dashboard-panel :project-id="$this->project->id" lazy />
            </div>
        </div>
    </div>

    @include('livewire.tasks.partials.bulk-actions-toolbar', [
        'selectedCount' => count($selectedTaskIds),
        'statuses' => $this->statuses,
    ])

    {{-- Bulk-delete confirmation — same modal pattern as the sidebar's
         delete-project / delete-chat confirms. --}}
    <x-dashy.modal name="confirm-bulk-delete" focusable class="max-w-md">
        <div class="space-y-4">
            <x-dashy.heading size="lg">{{ __('Delete selected tasks?') }}</x-dashy.heading>
            <x-dashy.subheading>
                {{ __('This permanently deletes the selected tasks. It cannot be undone.') }}
            </x-dashy.subheading>
            <div class="flex justify-end gap-2">
                <x-dashy.modal.close>
                    <x-dashy.button type="button" variant="filled" data-test="cancel-bulk-delete">
                        {{ __('Cancel') }}
                    </x-dashy.button>
                </x-dashy.modal.close>
                <x-dashy.button variant="danger" wire:click="bulkDelete" data-test="confirm-bulk-delete">
                    {{ __('Delete') }}
                </x-dashy.button>
            </div>
        </div>
    </x-dashy.modal>

    <livewire:tasks.task-detail-drawer :taskId="$initialTaskId" />
    @include('livewire.tasks.partials.task-create-drawer')
</div>
