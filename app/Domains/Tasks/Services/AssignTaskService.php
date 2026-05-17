<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\AddTaskAssigneeAction;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class AssignTaskService
{
    public function __construct(
        private FindTaskAction $find,
        private AddTaskAssigneeAction $add,
    ) {}

    public function execute(User $actor, int $taskId, int $userId): Task
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('update', $task);

        $isMember = $task->project->team->members()->whereKey($userId)->exists();

        if (! $isMember) {
            throw ValidationException::withMessages([
                'user_id' => __('The selected user is not a member of this team.'),
            ]);
        }

        DB::transaction(fn () => $this->add->execute($task, $userId, $actor->id));

        return $task->refresh();
    }
}
