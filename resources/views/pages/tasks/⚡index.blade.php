<?php

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Projects\Services\FindProjectService;
use App\Domains\Projects\Services\FindProjectWithTeamMembersService;
use App\Domains\Projects\Services\ListProjectsForUserService;
use App\Domains\Projects\Services\ListProjectStatusesForProjectService;
use App\Domains\Projects\Services\ListProjectStatusesForUserService;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ArchiveTaskService;
use App\Domains\Tasks\Services\AssignTaskService;
use App\Domains\Tasks\Services\CreateTaskService;
use App\Domains\Tasks\Services\DeleteTaskService;
use App\Domains\Tasks\Services\FindTaskService;
use App\Domains\Tasks\Services\ListAllTasksForUserService;
use App\Domains\Tasks\Services\MoveTaskService;
use App\Domains\Tasks\Services\ReorderTasksService;
use App\Domains\Tasks\Services\TaskExistsInProjectService;
use App\Domains\Tasks\Services\ToggleTaskCompleteService;
use App\Domains\Tasks\Services\UnarchiveTaskService;
use App\Domains\Tasks\Services\UnassignTaskService;
use App\Domains\Tasks\Services\UpdateTaskDatesService;
use App\Domains\Tasks\Services\UpdateTaskPriorityService;
use App\Domains\Tasks\Services\UpdateTaskStatusService;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\FindTeamForUserService;
use App\Domains\Teams\Services\ListTeamsForUserService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('All tasks')] class extends Component
{
    use DispatchesDashyUi;

    #[Url(as: 'team', keep: false)]
    public ?int $teamId = null;

    #[Url(as: 'archived', keep: false)]
    public bool $showArchived = false;

    /** @var array<int,string> */
    public array $collapsedStatusKeys = [];

    // Create-task drawer form state
    public bool $createOpen = false;

    public ?int $createProjectId = null;

    public ?int $createStatusId = null;

    public string $createName = '';

    public string $createDescription = '';

    public string $createPriority = 'normal';

    public ?string $createStartDate = null;

    public ?string $createEndDate = null;

    /** @var array<int,int> */
    public array $createAssigneeIds = [];

    public ?int $initialTaskId = null;

    public function mount(): void
    {
        $requestedTaskId = request()->query('task');
        if (is_numeric($requestedTaskId)) {
            $taskId = (int) $requestedTaskId;
            try {
                app(FindTaskService::class)->execute(Auth::user(), $taskId);
                $this->initialTaskId = $taskId;
            } catch (\Throwable) {
                // Task missing or no access — silently skip drawer hydration.
            }
        }
    }

    /** @return Collection<int, Team> */
    #[Computed]
    public function teams(): Collection
    {
        return app(ListTeamsForUserService::class)->execute(Auth::user());
    }

    #[Computed]
    public function activeTeam(): ?Team
    {
        if ($this->teamId === null) {
            return null;
        }

        try {
            return app(FindTeamForUserService::class)->execute(Auth::user(), $this->teamId);
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return Collection<int, Task> */
    #[Computed]
    public function allTasks(): Collection
    {
        return app(ListAllTasksForUserService::class)
            ->execute(Auth::user(), $this->activeTeam, $this->showArchived);
    }

    /**
     * Tasks shown in the body — narrowed by team filter and (always) archived flag.
     *
     * @return Collection<int, Task>
     */
    #[Computed]
    public function visibleTasks(): Collection
    {
        return $this->allTasks;
    }

    /** @return Collection<int, Project> */
    #[Computed]
    public function projects(): Collection
    {
        return app(ListProjectsForUserService::class)->execute(Auth::user());
    }

    /** @return array<int, \Illuminate\Support\Collection<int, Project>> */
    #[Computed]
    public function projectsByTeamId(): array
    {
        return $this->projects->groupBy('team_id')->all();
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

    /**
     * Unfiltered (team-agnostic) tasks so counts for inactive chips stay correct.
     *
     * @return Collection<int, Task>
     */
    #[Computed]
    public function allTasksAcrossTeams(): Collection
    {
        return app(ListAllTasksForUserService::class)
            ->execute(Auth::user(), null, $this->showArchived);
    }

    /**
     * Buckets for the status-summary row and the section list, grouped by status name.
     * Seeded from every status defined in the user's projects so empty buckets
     * still render (matches the mockup where Backlog/To do/In progress/In review/
     * Done all appear even with zero tasks in some of them).
     *
     * @return array<int, array{key:string,label:string,count:int,colorVar:string,anchor:string,tasks:Collection<int,Task>}>
     */
    #[Computed]
    public function statusBuckets(): array
    {
        $canonical = ['backlog' => 0, 'to do' => 1, 'in progress' => 2, 'in review' => 3, 'done' => 4];

        $grouped = [];

        // 1) Seed buckets from every status definition (so empty sections render).
        $statuses = app(ListProjectStatusesForUserService::class)
            ->execute(Auth::user(), $this->activeTeam);
        foreach ($statuses as $status) {
            $normalized = Str::lower(trim($status->name));
            $grouped[$normalized] ??= [
                'key' => Str::slug($normalized) ?: 'no-status',
                'label' => $status->name,
                'tasks' => [],
                'categories' => [],
            ];
            if ($status->category) {
                $grouped[$normalized]['categories'][] = $status->category->value;
            }
        }

        // 2) Layer task counts on top.
        foreach ($this->visibleTasks as $task) {
            $statusName = $task->status?->name ?? __('No status');
            $normalized = Str::lower(trim($statusName));
            $grouped[$normalized] ??= [
                'key' => Str::slug($normalized) ?: 'no-status',
                'label' => $statusName,
                'tasks' => [],
                'categories' => [],
            ];
            $grouped[$normalized]['tasks'][] = $task;
            if ($task->status?->category) {
                $grouped[$normalized]['categories'][] = $task->status->category->value;
            }
        }

        $buckets = [];
        foreach ($grouped as $normalized => $data) {
            $modeCategory = $this->modeCategoryValue($data['categories']);
            $category = ProjectStatusCategory::tryFrom($modeCategory ?? '') ?? ProjectStatusCategory::Active;
            $buckets[] = [
                'key' => $data['key'],
                'normalized' => $normalized,
                'label' => $data['label'],
                'count' => count($data['tasks']),
                'colorVar' => $category->colorVar(),
                'anchor' => 'status-'.$data['key'],
                'tasks' => collect($data['tasks']),
            ];
        }

        usort($buckets, function ($a, $b) use ($canonical) {
            $rankA = $canonical[$a['normalized']] ?? 99;
            $rankB = $canonical[$b['normalized']] ?? 99;
            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }

            return strcmp($a['label'], $b['label']);
        });

        return $buckets;
    }

    #[Computed]
    public function openCount(): int
    {
        return $this->visibleTasks->filter(function (Task $t) {
            $cat = $t->status?->category;

            return $cat !== ProjectStatusCategory::Done && $cat !== ProjectStatusCategory::Closed;
        })->count();
    }

    #[Computed]
    public function doneCount(): int
    {
        return $this->visibleTasks->filter(function (Task $t) {
            $cat = $t->status?->category;

            return $cat === ProjectStatusCategory::Done || $cat === ProjectStatusCategory::Closed;
        })->count();
    }

    /**
     * Statuses for the currently picked project in the create drawer.
     *
     * @return SupportCollection<int, ProjectStatus>
     */
    #[Computed]
    public function createProjectStatuses(): SupportCollection
    {
        if ($this->createProjectId === null) {
            return collect();
        }

        try {
            $project = app(FindProjectService::class)->execute(Auth::user(), $this->createProjectId);

            return collect(app(ListProjectStatusesForProjectService::class)->execute(Auth::user(), $project)->all());
        } catch (\Throwable) {
            return collect();
        }
    }

    /** @return SupportCollection<int, \App\Models\User> */
    #[Computed]
    public function createProjectMembers(): SupportCollection
    {
        if ($this->createProjectId === null) {
            return collect();
        }

        try {
            $project = app(FindProjectWithTeamMembersService::class)->execute(Auth::user(), $this->createProjectId);

            return collect($project->team->members->all());
        } catch (\Throwable) {
            return collect();
        }
    }

    public function openCreateTask(?int $statusId = null, ?int $projectId = null): void
    {
        $this->resetCreateForm();
        $this->createProjectId = $projectId ?? $this->projects->first()?->id;
        $this->createStatusId = $statusId ?? $this->createProjectStatuses->first()?->id;
        $this->createOpen = true;

        $this->openModal('task-create');
    }

    public function updatedCreateProjectId(): void
    {
        $this->createStatusId = $this->createProjectStatuses->first()?->id;
        $this->createAssigneeIds = [];
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

    public function submitCreateTask(CreateTaskService $create, AssignTaskService $assign, FindProjectService $findProject): void
    {
        if ($this->createStatusId === null || $this->createProjectId === null) {
            return;
        }

        $project = $findProject->execute(Auth::user(), $this->createProjectId);

        $task = $create->execute(Auth::user(), $project, [
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
        $this->createProjectId = null;
        $this->createStatusId = null;
        $this->createName = '';
        $this->createDescription = '';
        $this->createPriority = TaskPriority::Normal->value;
        $this->createStartDate = null;
        $this->createEndDate = null;
        $this->createAssigneeIds = [];
        $this->resetErrorBag();
    }

    public function toggleComplete(int $taskId, ToggleTaskCompleteService $svc): void
    {
        $svc->execute(Auth::user(), $taskId);
        $this->dispatch('task-list-changed');
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

    public function openTaskDetail(int $taskId): void
    {
        $this->dispatch('task-detail:open', taskId: $taskId);
    }

    /**
     * Reorder within an aggregator bucket. The bucket may contain tasks from
     * multiple projects/statuses; we only persist a new order for the dragged
     * task's same-(project, status) peers — cross-project ordering has no
     * meaningful storage.
     *
     * @param  list<int|string>  $orderedIds  Post-drop bucket order from Sortable.
     */
    public function aggregatorReorderBucket(int $taskId, array $orderedIds, ReorderTasksService $svc): void
    {
        $task = app(FindTaskService::class)->execute(Auth::user(), $taskId);

        if ($task->project_status_id === null) {
            return;
        }

        // The service filters orderedIds down to same-project/status peers
        // before reordering; the aggregator view spans projects.
        $svc->execute(Auth::user(), $task->project_status_id, $orderedIds);
        $this->dispatch('task-list-changed');
    }

    /**
     * Move a task across aggregator buckets. The target bucket key is the
     * slug of a normalized status name; we resolve it to a real ProjectStatus
     * in the dragged task's project. Same-project peers in source/target
     * buckets get reordered.
     *
     * @param  list<int|string>  $sourceIds
     * @param  list<int|string>  $targetIds
     */
    public function aggregatorMoveBetweenBuckets(
        int $taskId,
        string $targetBucketKey,
        array $sourceIds,
        array $targetIds,
        MoveTaskService $svc,
        ListProjectStatusesForProjectService $listStatuses,
    ): void {
        $task = app(FindTaskService::class)->execute(Auth::user(), $taskId);

        $statuses = $listStatuses->execute(Auth::user(), $task->project);

        $targetStatus = $statuses->first(function (ProjectStatus $s) use ($targetBucketKey) {
            $slug = Str::slug(Str::lower(trim($s->name))) ?: 'no-status';

            return $slug === $targetBucketKey;
        });

        if ($targetStatus === null) {
            $this->toast('error', __('No matching status in this project for that bucket.'));
            $this->dispatch('task-list-changed');

            return;
        }

        // MoveTaskService filters source/target IDs down to same-project peers
        // and always re-adds the dragged task to the target list, so the
        // aggregator can pass the raw client-supplied arrays through.
        $svc->execute(Auth::user(), $taskId, $targetStatus->id, $sourceIds, $targetIds);
        $this->dispatch('task-list-changed');
    }

    public function deleteTask(int $taskId, DeleteTaskService $svc): void
    {
        $svc->execute(Auth::user(), $taskId);
        $this->toast('success', __('Task deleted.'));
        $this->dispatch('task-list-changed');
    }

    public function toggleArchivedVisibility(): void
    {
        $this->showArchived = ! $this->showArchived;
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

    public function toggleStatusCollapseByKey(string $key): void
    {
        $idx = array_search($key, $this->collapsedStatusKeys, true);
        if ($idx === false) {
            $this->collapsedStatusKeys[] = $key;

            return;
        }

        unset($this->collapsedStatusKeys[$idx]);
        $this->collapsedStatusKeys = array_values($this->collapsedStatusKeys);
    }

    public function openCreateProjectModal(): void
    {
        // The app-sidebar's Livewire component owns the create-project flow.
        // From the standalone aggregator page we just dispatch the same event
        // it already listens for.
        $this->dispatch('open-create-project');
    }

    /** @param  list<string>  $values */
    private function modeCategoryValue(array $values): ?string
    {
        if ($values === []) {
            return null;
        }

        $counts = array_count_values($values);
        arsort($counts);

        return array_key_first($counts);
    }

    #[On('task-list-changed')]
    public function refresh(): void
    {
        // Re-renders so computeds re-evaluate.
    }

    #[On('time-entries-updated')]
    public function refreshTaskTotals(): void
    {
        // Re-renders so totals refresh.
    }
}; ?>

<div class="flex min-h-0 flex-1 flex-col" data-test="tasks-index-page">
    @include('livewire.tasks.partials.page-top-bar', [
        'teams' => $this->teams,
        'teamCounts' => $this->teamCounts,
        'totalCount' => $this->allTasksAcrossTeams->count(),
        'activeTeamId' => $this->teamId,
        'everythingHref' => route('tasks'),
    ])

    <div class="mx-auto flex w-full max-w-screen-2xl flex-1 min-h-0 flex-col gap-4 overflow-y-auto px-4 py-4 lg:flex-row lg:gap-6 lg:overflow-hidden lg:px-6 lg:py-0">
        @include('livewire.tasks.partials.workspace-sidebar', [
            'teams' => $this->teams,
            'projectsByTeamId' => $this->projectsByTeamId,
            'projectTaskCounts' => $this->projectTaskCounts,
            'totalCount' => $this->allTasksAcrossTeams->count(),
            'activeProjectId' => null,
            'isEverythingActive' => true,
            'activeTeam' => $this->activeTeam,
            'activeTeamCount' => $this->activeTeam
                ? (int) ($this->teamCounts[$this->activeTeam->id] ?? 0)
                : 0,
        ])

        <div class="flex min-w-0 flex-1 flex-col gap-4 lg:h-full lg:overflow-y-auto lg:py-4">
            @include('livewire.tasks.partials.page-heading', [
                'breadcrumb' => [
                    ['label' => __('Workspace'), 'href' => route('tasks')],
                    [
                        'label' => $this->activeTeam ? $this->activeTeam->name : __('Everything'),
                        'href' => null,
                    ],
                ],
                'project' => null,
                'title' => $this->activeTeam ? $this->activeTeam->name : __('All tasks'),
                'showArchived' => $this->showArchived,
                'settingsTeamId' => $this->activeTeam?->id,
            ])

            <div class="flex flex-col gap-3">
                @forelse ($this->statusBuckets as $bucket)
                    @php
                        $isCollapsed = in_array($bucket['key'], $this->collapsedStatusKeys, true);
                        $bucketHasTasks = count($bucket['tasks']) > 0;
                    @endphp
                    <section id="{{ $bucket['anchor'] }}"
                             class="flex flex-col gap-2"
                             wire:key="bucket-{{ $bucket['key'] }}"
                             data-test="aggregator-bucket-{{ $bucket['key'] }}">
                        <header class="flex items-center justify-between gap-2 px-1 py-1">
                            <button
                                type="button"
                                wire:click="toggleStatusCollapseByKey('{{ $bucket['key'] }}')"
                                class="flex items-center gap-2 rounded-md px-1 py-1 transition focus:outline-none focus-visible:ring-2"
                                style="--tw-ring-color: var(--blue);"
                                aria-expanded="{{ $isCollapsed ? 'false' : 'true' }}"
                            >
                                <x-dashy.icon
                                    :name="$isCollapsed ? 'chevron-right' : 'chevron-down'"
                                    class="size-3.5 shrink-0"
                                    style="color: var(--ink-dim);"
                                />
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-medium"
                                    style="background-color: color-mix(in srgb, var({{ $bucket['colorVar'] }}) 14%, transparent); color: color-mix(in srgb, var({{ $bucket['colorVar'] }}) 80%, var(--ink));"
                                >
                                    <span class="inline-block size-1.5 rounded-full" style="background-color: var({{ $bucket['colorVar'] }});"></span>
                                    <span>{{ $bucket['label'] }}</span>
                                </span>
                                <span class="text-xs" style="color: var(--ink-dim);">{{ $bucket['count'] }}</span>
                            </button>

                            <button
                                type="button"
                                wire:click="openCreateTask"
                                class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs transition"
                                style="color: var(--ink-dim);"
                                onmouseover="this.style.color='var(--blue)'; this.style.backgroundColor='color-mix(in srgb, var(--blue) 10%, transparent)';"
                                onmouseout="this.style.color='var(--ink-dim)'; this.style.backgroundColor='transparent';"
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
                                            group: 'aggregator-tasks',
                                            handle: '.task-drag-handle',
                                            draggable: '[data-task-id]',
                                            onEnd: (evt) => {
                                                const taskId = parseInt(evt.item.dataset.taskId, 10);
                                                const fromKey = evt.from.dataset.bucketKey;
                                                const toKey = evt.to.dataset.bucketKey;
                                                const sourceIds = [...evt.from.children].map(el => parseInt(el.dataset.taskId, 10)).filter(Number.isInteger);
                                                const targetIds = [...evt.to.children].map(el => parseInt(el.dataset.taskId, 10)).filter(Number.isInteger);
                                                if (fromKey === toKey) {
                                                    $wire.aggregatorReorderBucket(taskId, targetIds);
                                                } else {
                                                    $wire.aggregatorMoveBetweenBuckets(taskId, toKey, sourceIds, targetIds);
                                                }
                                            },
                                        });
                                    }
                                "
                                wire:ignore.self
                                data-bucket-key="{{ $bucket['key'] }}"
                                data-test="aggregator-sortable-{{ $bucket['key'] }}"
                                @class([
                                    'flex flex-col overflow-hidden',
                                    'rounded-xl' => $bucketHasTasks,
                                ])
                                style="
                                    min-height: {{ $bucketHasTasks ? '0' : '8px' }};
                                    background-color: {{ $bucketHasTasks ? 'var(--surface)' : 'transparent' }};
                                    box-shadow: {{ $bucketHasTasks
                                        ? '0 1px 2px rgba(var(--ink-rgb), 0.04), 0 0 0 1px var(--border) inset'
                                        : 'none' }};
                                "
                            >
                                @foreach ($bucket['tasks'] as $task)
                                    @include('livewire.tasks.partials.task-row-card', [
                                        'task' => $task,
                                        'teamMembers' => $task->project->team->members,
                                        'allStatuses' => $task->project->statuses,
                                        'showProjectPill' => true,
                                        'showStatusPill' => false,
                                        'showCheckbox' => false,
                                        'showDragHandle' => true,
                                        'plainMeta' => true,
                                    ])
                                @endforeach
                            </div>
                        @endif
                    </section>
                @empty
                    <div class="flex flex-col items-center justify-center gap-3 py-12 text-center" data-test="tasks-index-empty">
                        <div class="flex size-12 items-center justify-center rounded-xl"
                             style="background-color: var(--surface-2); color: var(--ink-dim);">
                            <x-dashy.icon name="clipboard-document-check" class="size-6" />
                        </div>
                        <p class="text-sm" style="color: var(--ink-muted);">
                            {{ __('No tasks yet. Create your first task to see it here.') }}
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <livewire:tasks.task-detail-drawer :taskId="$initialTaskId" />
    @include('livewire.tasks.partials.task-create-drawer-aggregator')
</div>
