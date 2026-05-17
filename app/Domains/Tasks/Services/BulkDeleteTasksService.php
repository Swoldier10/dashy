<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\DeleteTaskAction;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Hard-deletes a batch of tasks atomically. Authorisation is checked per
 * task; any failure rolls the whole batch back so partial deletes cannot
 * happen. Returns the number of tasks that were deleted.
 */
final class BulkDeleteTasksService
{
    public function __construct(
        private FindTaskAction $find,
        private DeleteTaskAction $delete,
    ) {}

    /**
     * @param  list<int>  $taskIds
     */
    public function execute(User $actor, array $taskIds): int
    {
        $taskIds = array_values(array_unique(array_map(fn ($id) => (int) $id, $taskIds)));
        if ($taskIds === []) {
            throw ValidationException::withMessages([
                'task_ids' => __('Provide at least one task id.'),
            ]);
        }

        return DB::transaction(function () use ($actor, $taskIds): int {
            $count = 0;

            foreach ($taskIds as $taskId) {
                $task = $this->find->execute($taskId);
                Gate::forUser($actor)->authorize('delete', $task);
                $this->delete->execute($task);
                $count++;
            }

            return $count;
        });
    }
}
