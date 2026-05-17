<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\MoveTaskToStatusAction;
use App\Domains\Tasks\Actions\NextTaskPositionAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Moves a batch of tasks to a single target status, atomically. The whole
 * batch fails together: if any task is missing, unauthorised, or belongs to a
 * different project than the target status, the transaction is rolled back
 * and no tasks are moved.
 */
final class BulkMoveTasksService
{
    public function __construct(
        private FindTaskAction $find,
        private MoveTaskToStatusAction $move,
        private NextTaskPositionAction $nextPosition,
    ) {}

    /**
     * @param  list<int>  $taskIds
     * @return Collection<int, Task>
     */
    public function execute(User $actor, array $taskIds, int $targetStatusId): Collection
    {
        $taskIds = array_values(array_unique(array_map(fn ($id) => (int) $id, $taskIds)));
        if ($taskIds === []) {
            throw ValidationException::withMessages([
                'task_ids' => __('Provide at least one task id.'),
            ]);
        }

        return DB::transaction(function () use ($actor, $taskIds, $targetStatusId): Collection {
            $targetStatus = ProjectStatus::query()->find($targetStatusId);
            if ($targetStatus === null) {
                throw ValidationException::withMessages([
                    'project_status_id' => __('The target status does not exist.'),
                ]);
            }

            /** @var Collection<int, Task> $moved */
            $moved = new Collection;

            foreach ($taskIds as $taskId) {
                $task = $this->find->execute($taskId);
                Gate::forUser($actor)->authorize('update', $task);

                if ($task->project_id !== $targetStatus->project_id) {
                    throw ValidationException::withMessages([
                        'project_status_id' => __('All tasks must share the target status\'s project.'),
                    ]);
                }

                if ($task->project_status_id === $targetStatusId) {
                    $moved->push($task);

                    continue;
                }

                $position = $this->nextPosition->execute($task->project_id, $targetStatusId);
                $moved->push($this->move->execute($task, $targetStatusId, $position));
            }

            return $moved;
        });
    }
}
