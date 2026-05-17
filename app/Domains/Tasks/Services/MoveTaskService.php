<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\MoveTaskToStatusAction;
use App\Domains\Tasks\Actions\ReorderTasksAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class MoveTaskService
{
    public function __construct(
        private FindTaskAction $find,
        private MoveTaskToStatusAction $move,
        private ReorderTasksAction $reorder,
    ) {}

    /**
     * Cross-status drag. Moves $taskId to $targetStatusId, then rewrites
     * positions for both source and target groups using the post-drop
     * arrays produced by the frontend Sortable instance.
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

        $statusBelongs = ProjectStatus::query()
            ->where('id', $targetStatusId)
            ->where('project_id', $task->project_id)
            ->exists();

        if (! $statusBelongs) {
            throw ValidationException::withMessages([
                'project_status_id' => __('The selected status does not belong to this project.'),
            ]);
        }

        $sourceStatusId = $task->project_status_id;
        $targetIndex = array_search($task->id, array_map('intval', $targetOrderedIds), true);
        $newPosition = $targetIndex === false ? 0 : $targetIndex;

        return DB::transaction(function () use (
            $task, $targetStatusId, $newPosition,
            $sourceStatusId, $sourceOrderedIds, $targetOrderedIds
        ) {
            $moved = $this->move->execute($task, $targetStatusId, $newPosition);

            $this->reorder->execute($task->project_id, $sourceStatusId, $sourceOrderedIds);
            $this->reorder->execute($task->project_id, $targetStatusId, $targetOrderedIds);

            return $moved->refresh();
        });
    }
}
