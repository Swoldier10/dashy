<?php

namespace App\Livewire\Tasks;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Projects\Services\ListProjectStatusesForProjectService;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\AssignTaskService;
use App\Domains\Tasks\Services\DeleteTaskService;
use App\Domains\Tasks\Services\FindTaskService;
use App\Domains\Tasks\Services\UnassignTaskService;
use App\Domains\Tasks\Services\UpdateTaskDatesService;
use App\Domains\Tasks\Services\UpdateTaskPriorityService;
use App\Domains\Tasks\Services\UpdateTaskService;
use App\Domains\Tasks\Services\UpdateTaskStatusService;
use App\Domains\Teams\Services\ListTeamMembersService;
use App\Models\User;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskDetailDrawer extends Component
{
    use DispatchesDashyUi;

    public ?int $taskId = null;

    public function mount(?int $taskId = null): void
    {
        // Allow parents to hydrate the drawer on initial render (deep-link).
        // Without this, dispatching `task-detail:open` from a parent's mount()
        // wouldn't be observed by the child on the initial render — the event
        // fires after the page is built, leaving the drawer empty on first
        // paint.
        if ($taskId !== null && $taskId > 0) {
            $this->open($taskId);
        }
    }

    public string $detailName = '';

    public string $detailDescription = '';

    public ?int $detailStatusId = null;

    public string $detailPriority = 'normal';

    public ?string $detailStartDate = null;

    public ?string $detailEndDate = null;

    #[On('task-detail:open')]
    public function open(int $taskId): void
    {
        $task = app(FindTaskService::class)->execute(Auth::user(), $taskId);

        $this->taskId = $task->id;
        $this->detailName = (string) $task->name;
        $this->detailDescription = (string) ($task->description ?? '');
        $this->detailStatusId = $task->project_status_id;
        $this->detailPriority = ($task->priority instanceof TaskPriority
            ? $task->priority->value
            : (string) $task->priority) ?: TaskPriority::Normal->value;
        $this->detailStartDate = $task->start_date?->format('Y-m-d\TH:i');
        $this->detailEndDate = $task->end_date?->format('Y-m-d\TH:i');

        $this->openModal('task-detail');
    }

    public function closeTaskDetail(): void
    {
        // Intentionally a no-op — see the morph-null gotcha at
        // resources/views/pages/tasks/⚡show.blade.php:288-298. The drawer is
        // hidden by Alpine's x-show; tearing the nested time-panel out by
        // nulling $taskId would flip the @if branch and crash Livewire's morph.
    }

    #[Computed]
    public function task(): ?Task
    {
        if ($this->taskId === null) {
            return null;
        }

        try {
            return app(FindTaskService::class)->execute(Auth::user(), $this->taskId);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    /** @return Collection<int, ProjectStatus> */
    #[Computed]
    public function statuses(): Collection
    {
        $task = $this->task;
        if ($task === null) {
            return new Collection;
        }

        // Reverse-enum order so the most-advanced statuses come first
        // (closed → done → active → not started). Same shape as the
        // tasks page's statuses() computed.
        $rank = array_flip(array_column(ProjectStatusCategory::cases(), 'value'));

        return app(ListProjectStatusesForProjectService::class)
            ->execute(Auth::user(), $task->project)
            ->sortByDesc(fn ($s) => $rank[$s->category->value] ?? -1)
            ->values();
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function teamMembers(): Collection
    {
        $task = $this->task;
        if ($task === null) {
            return new Collection;
        }

        return app(ListTeamMembersService::class)
            ->execute(Auth::user(), (int) $task->project->team_id);
    }

    public function saveTaskDetail(UpdateTaskService $svc): void
    {
        if ($this->taskId === null) {
            return;
        }

        $svc->execute(Auth::user(), $this->taskId, [
            'name' => $this->detailName,
            'description' => $this->detailDescription !== '' ? $this->detailDescription : null,
        ]);

        app(UpdateTaskDatesService::class)->execute(
            Auth::user(),
            $this->taskId,
            $this->detailStartDate ?: null,
            $this->detailEndDate ?: null,
        );

        $this->dispatch('task-list-changed');
        $this->dispatch('calendar-events-changed');
    }

    public function updatedDetailStatusId($value): void
    {
        if ($this->taskId === null || $value === null || $value === '') {
            return;
        }

        app(UpdateTaskStatusService::class)->execute(Auth::user(), $this->taskId, (int) $value);

        $this->dispatch('task-list-changed');
        $this->dispatch('calendar-events-changed');
    }

    public function updatedDetailPriority($value): void
    {
        if ($this->taskId === null || $value === null || $value === '') {
            return;
        }

        app(UpdateTaskPriorityService::class)->execute(Auth::user(), $this->taskId, (string) $value);

        $this->dispatch('task-list-changed');
        $this->dispatch('calendar-events-changed');
    }

    public function toggleAssignee(int $userId, AssignTaskService $assign, UnassignTaskService $unassign): void
    {
        if ($this->taskId === null) {
            return;
        }

        $task = app(FindTaskService::class)->execute(Auth::user(), $this->taskId);

        if ($task->assignees->contains('id', $userId)) {
            $unassign->execute(Auth::user(), $this->taskId, $userId);
        } else {
            $assign->execute(Auth::user(), $this->taskId, $userId);
        }

        $this->dispatch('task-list-changed');
        $this->dispatch('calendar-events-changed');
    }

    public function deleteTask(DeleteTaskService $svc): void
    {
        if ($this->taskId === null) {
            return;
        }

        $svc->execute(Auth::user(), $this->taskId);

        $this->closeModal('task-detail');
        $this->toast('success', __('Task deleted.'));
        $this->dispatch('task-list-changed');
        $this->dispatch('calendar-events-changed');
    }

    #[On('task-list-changed')]
    public function refreshTask(): void
    {
        // Empty — re-renders so the task() computed re-evaluates.
    }

    public function render()
    {
        return view('livewire.tasks.task-detail-drawer');
    }
}
