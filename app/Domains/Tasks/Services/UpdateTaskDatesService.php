<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\UpdateTaskAction;
use App\Domains\Tasks\Events\TaskDueDateChanged;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

final class UpdateTaskDatesService
{
    public function __construct(
        private FindTaskAction $find,
        private UpdateTaskAction $update,
    ) {}

    public function execute(User $actor, int $taskId, ?string $startDate, ?string $endDate): Task
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('update', $task);

        $validated = Validator::make([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ], [
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ])->validate();

        $oldEndDate = $task->end_date?->toIso8601String();
        $assigneeIds = $task->assignees->pluck('id')->all();

        return DB::transaction(function () use ($task, $actor, $validated, $oldEndDate, $assigneeIds): Task {
            $updated = $this->update->execute($task, [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]);

            $newEndDate = $updated->end_date?->toIso8601String();

            if ($newEndDate !== $oldEndDate) {
                DB::afterCommit(fn () => event(TaskDueDateChanged::fromTask(
                    $updated, $actor, $oldEndDate, $newEndDate, $assigneeIds,
                )));
            }

            return $updated;
        });
    }
}
