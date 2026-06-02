<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\UpdateTaskAction;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Events\TaskPriorityChanged;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class UpdateTaskPriorityService
{
    public function __construct(
        private FindTaskAction $find,
        private UpdateTaskAction $update,
    ) {}

    public function execute(User $actor, int $taskId, string $priority): Task
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('update', $task);

        $validated = Validator::make(['priority' => $priority], [
            'priority' => ['required', Rule::enum(TaskPriority::class)],
        ])->validate();

        $oldPriority = $task->priority?->value;
        $newPriority = (string) $validated['priority'];
        $assigneeIds = $task->assignees->pluck('id')->all();

        return DB::transaction(function () use ($task, $actor, $oldPriority, $newPriority, $assigneeIds): Task {
            $updated = $this->update->execute($task, ['priority' => $newPriority]);

            if ($newPriority !== $oldPriority) {
                DB::afterCommit(fn () => event(TaskPriorityChanged::fromTask(
                    $updated, $actor, $oldPriority, $newPriority, $assigneeIds,
                )));
            }

            return $updated;
        });
    }
}
