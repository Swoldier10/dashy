<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\RemoveTaskAssigneeAction;
use App\Domains\Tasks\Events\TaskUnassigned;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class UnassignTaskService
{
    public function __construct(
        private FindTaskAction $find,
        private RemoveTaskAssigneeAction $remove,
    ) {}

    public function execute(User $actor, int $taskId, int $userId): Task
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('update', $task);

        $wasAssigned = $task->assignees->contains('id', $userId);

        DB::transaction(function () use ($task, $actor, $userId, $wasAssigned): void {
            $this->remove->execute($task, $userId);

            if ($wasAssigned) {
                DB::afterCommit(fn () => event(TaskUnassigned::fromTask($task, $actor, $userId)));
            }
        });

        return $task->refresh();
    }
}
