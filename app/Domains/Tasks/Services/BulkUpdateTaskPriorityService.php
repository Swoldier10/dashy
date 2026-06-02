<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\UpdateTaskAction;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Sets the priority on a batch of tasks atomically. The actor must have
 * update rights on every task in the batch: if any task is missing or
 * unauthorised, the transaction is rolled back and no priorities change.
 */
final class BulkUpdateTaskPriorityService
{
    public function __construct(
        private FindTaskAction $find,
        private UpdateTaskAction $update,
    ) {}

    /**
     * @param  list<int>  $taskIds
     * @return Collection<int, Task>
     */
    public function execute(User $actor, array $taskIds, string $priority): Collection
    {
        $taskIds = array_values(array_unique(array_map(fn ($id) => (int) $id, $taskIds)));
        if ($taskIds === []) {
            throw ValidationException::withMessages([
                'task_ids' => __('Provide at least one task id.'),
            ]);
        }

        $validated = Validator::make(['priority' => $priority], [
            'priority' => ['required', Rule::enum(TaskPriority::class)],
        ])->validate();

        return DB::transaction(function () use ($actor, $taskIds, $validated): Collection {
            /** @var Collection<int, Task> $updated */
            $updated = new Collection;

            foreach ($taskIds as $taskId) {
                $task = $this->find->execute($taskId);
                Gate::forUser($actor)->authorize('update', $task);

                $updated->push($this->update->execute($task, ['priority' => $validated['priority']]));
            }

            return $updated;
        });
    }
}
