<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Projects\Services\AssertProjectStatusInProjectService;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\MoveTaskToStatusAction;
use App\Domains\Tasks\Actions\NextTaskPositionAction;
use App\Domains\Tasks\Events\TaskStatusChanged;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class UpdateTaskStatusService
{
    public function __construct(
        private FindTaskAction $find,
        private MoveTaskToStatusAction $move,
        private NextTaskPositionAction $nextPosition,
        private AssertProjectStatusInProjectService $assertStatusInProject,
    ) {}

    public function execute(User $actor, int $taskId, int $projectStatusId): Task
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('update', $task);

        $this->assertStatusInProject->execute($projectStatusId, $task->project_id);

        if ($task->project_status_id === $projectStatusId) {
            return $task;
        }

        $oldStatusName = $task->status?->name;
        $oldCategory = $task->status?->category?->value;
        $assigneeIds = $task->assignees->pluck('id')->all();

        return DB::transaction(function () use ($task, $actor, $projectStatusId, $oldStatusName, $oldCategory, $assigneeIds) {
            $position = $this->nextPosition->execute($task->project_id, $projectStatusId);

            $moved = $this->move->execute($task, $projectStatusId, $position)->refresh();

            DB::afterCommit(fn () => event(TaskStatusChanged::fromTask(
                $moved,
                $actor,
                $oldStatusName,
                $oldCategory,
                (string) $moved->status?->name,
                (string) $moved->status?->category?->value,
                $assigneeIds,
            )));

            return $moved;
        });
    }
}
