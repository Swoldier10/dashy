<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Projects\Services\AssertProjectStatusInProjectService;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\MoveTaskToStatusAction;
use App\Domains\Tasks\Actions\ReorderTasksAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class MoveTaskService
{
    public function __construct(
        private FindTaskAction $find,
        private MoveTaskToStatusAction $move,
        private ReorderTasksAction $reorder,
        private AssertProjectStatusInProjectService $assertStatusInProject,
    ) {}

    /**
     * Cross-status drag. Moves $taskId to $targetStatusId, then rewrites
     * positions for both source and target groups using the post-drop
     * arrays produced by the frontend Sortable instance.
     *
     * Forged/cross-project IDs in either array are filtered out by the
     * action's WHERE clauses (no manual filtering needed).
     *
     * @param  list<int|string>  $sourceOrderedIds  Task IDs remaining in the source group.
     * @param  list<int|string>  $targetOrderedIds  Task IDs of the target group, including the moved task.
     */
    public function execute(
        User $actor,
        int $taskId,
        int $targetStatusId,
        array $sourceOrderedIds,
        array $targetOrderedIds,
    ): Task {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('update', $task);

        $this->assertStatusInProject->execute($targetStatusId, $task->project_id);

        $sourceStatusId = $task->project_status_id;
        $targetIndex = array_search($task->id, array_map('intval', $targetOrderedIds), true);
        $newPosition = $targetIndex === false ? 0 : $targetIndex;

        return DB::transaction(function () use (
            $task, $targetStatusId, $newPosition,
            $sourceStatusId, $sourceOrderedIds, $targetOrderedIds,
        ) {
            $moved = $this->move->execute($task, $targetStatusId, $newPosition);

            if ($sourceStatusId !== null) {
                $this->reorder->execute($task->project_id, $sourceStatusId, $sourceOrderedIds);
            }
            $this->reorder->execute($task->project_id, $targetStatusId, $targetOrderedIds);

            return $moved->refresh();
        });
    }
}
