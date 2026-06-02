<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\UpdateTaskAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Sets the due date (end_date) on a batch of tasks atomically.
 *
 * With a due date, each task keeps its own start_date — except when that
 * start is after the new due date, in which case the start is cleared so
 * the task stays valid (due >= start) and the batch succeeds. With null,
 * both start_date and end_date are cleared on every task, mirroring the
 * single-task "Clear dates" behaviour.
 *
 * The actor must have update rights on every task in the batch: if any
 * task is missing or unauthorised, the transaction is rolled back and no
 * dates change.
 */
final class BulkUpdateTaskDueDateService
{
    public function __construct(
        private FindTaskAction $find,
        private UpdateTaskAction $update,
    ) {}

    /**
     * @param  list<int>  $taskIds
     * @return Collection<int, Task>
     */
    public function execute(User $actor, array $taskIds, ?string $dueDate): Collection
    {
        $taskIds = array_values(array_unique(array_map(fn ($id) => (int) $id, $taskIds)));
        if ($taskIds === []) {
            throw ValidationException::withMessages([
                'task_ids' => __('Provide at least one task id.'),
            ]);
        }

        $validated = Validator::make(['due_date' => $dueDate], [
            'due_date' => ['nullable', 'date'],
        ])->validate();

        $due = ($validated['due_date'] ?? null) !== null
            ? Carbon::parse($validated['due_date'])
            : null;

        return DB::transaction(function () use ($actor, $taskIds, $due): Collection {
            /** @var Collection<int, Task> $updated */
            $updated = new Collection;

            foreach ($taskIds as $taskId) {
                $task = $this->find->execute($taskId);
                Gate::forUser($actor)->authorize('update', $task);

                if ($due === null) {
                    $updated->push($this->update->execute($task, [
                        'start_date' => null,
                        'end_date' => null,
                    ]));

                    continue;
                }

                $startConflicts = $task->start_date !== null && $task->start_date->gt($due);

                $updated->push($this->update->execute($task, [
                    'start_date' => $startConflicts ? null : $task->start_date,
                    'end_date' => $due,
                ]));
            }

            return $updated;
        });
    }
}
