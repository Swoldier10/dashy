<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Projects\Actions\ListProjectStatusesForProjectAction;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class ToggleTaskCompleteService
{
    public function __construct(
        private FindTaskAction $find,
        private ListProjectStatusesForProjectAction $listStatuses,
        private UpdateTaskStatusService $updateStatus,
    ) {}

    public function execute(User $actor, int $taskId): Task
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('update', $task);

        $statuses = $this->listStatuses->execute($task->project);

        $current = $task->status;
        $isCurrentlyDone = $current
            && in_array($current->category, [
                ProjectStatusCategory::Done,
                ProjectStatusCategory::Closed,
            ], true);

        if ($isCurrentlyDone) {
            $target = $this->firstInCategories($statuses, [
                ProjectStatusCategory::Active,
                ProjectStatusCategory::NotStarted,
            ]) ?? $statuses->first();
        } else {
            $target = $this->firstInCategories($statuses, [ProjectStatusCategory::Done]);
        }

        if ($target === null) {
            throw ValidationException::withMessages([
                'project_status_id' => __('This project has no "Done" status to complete into.'),
            ]);
        }

        return $this->updateStatus->execute($actor, $taskId, $target->id);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, ProjectStatus>  $statuses
     * @param  list<ProjectStatusCategory>  $categories
     */
    private function firstInCategories($statuses, array $categories): ?ProjectStatus
    {
        foreach ($categories as $category) {
            $match = $statuses->first(fn (ProjectStatus $s) => $s->category === $category);
            if ($match) {
                return $match;
            }
        }

        return null;
    }
}
