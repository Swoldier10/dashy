<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\MoveTaskToStatusAction;
use App\Domains\Tasks\Actions\NextTaskPositionAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class UpdateTaskStatusService
{
    public function __construct(
        private FindTaskAction $find,
        private MoveTaskToStatusAction $move,
        private NextTaskPositionAction $nextPosition,
    ) {}

    public function execute(User $actor, int $taskId, int $projectStatusId): Task
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('update', $task);

        $statusBelongs = ProjectStatus::query()
            ->where('id', $projectStatusId)
            ->where('project_id', $task->project_id)
            ->exists();

        if (! $statusBelongs) {
            throw ValidationException::withMessages([
                'project_status_id' => __('The selected status does not belong to this project.'),
            ]);
        }

        if ($task->project_status_id === $projectStatusId) {
            return $task;
        }

        return DB::transaction(function () use ($task, $projectStatusId) {
            $position = $this->nextPosition->execute($task->project_id, $projectStatusId);

            return $this->move->execute($task, $projectStatusId, $position);
        });
    }
}
