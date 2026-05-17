<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\UpdateTaskAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Archives a batch of tasks atomically. Already-archived tasks pass through
 * unchanged. The actor must have update rights on every task in the batch.
 */
final class BulkArchiveTasksService
{
    public function __construct(
        private FindTaskAction $find,
        private UpdateTaskAction $update,
    ) {}

    /**
     * @param  list<int>  $taskIds
     * @return Collection<int, Task>
     */
    public function execute(User $actor, array $taskIds): Collection
    {
        $taskIds = array_values(array_unique(array_map(fn ($id) => (int) $id, $taskIds)));
        if ($taskIds === []) {
            throw ValidationException::withMessages([
                'task_ids' => __('Provide at least one task id.'),
            ]);
        }

        return DB::transaction(function () use ($actor, $taskIds): Collection {
            /** @var Collection<int, Task> $archived */
            $archived = new Collection;

            foreach ($taskIds as $taskId) {
                $task = $this->find->execute($taskId);
                Gate::forUser($actor)->authorize('update', $task);

                if ($task->is_archived) {
                    $archived->push($task);

                    continue;
                }

                $archived->push($this->update->execute($task, ['is_archived' => true]));
            }

            return $archived;
        });
    }
}
