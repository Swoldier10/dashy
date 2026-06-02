<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\AddTaskAssigneeAction;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Events\TaskAssigned;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Services\ListTeamMemberIdsService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class AssignTaskService
{
    public function __construct(
        private FindTaskAction $find,
        private AddTaskAssigneeAction $add,
        private ListTeamMemberIdsService $listTeamMemberIds,
    ) {}

    public function execute(User $actor, int $taskId, int $userId): Task
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('update', $task);

        $memberIds = $this->listTeamMemberIds->execute($task->project->team);

        if (! in_array($userId, $memberIds, true)) {
            throw ValidationException::withMessages([
                'user_id' => __('The selected user is not a member of this team.'),
            ]);
        }

        // Already assigned: no write, no notification (mirrors the bulk service).
        if ($task->assignees->contains('id', $userId)) {
            return $task;
        }

        DB::transaction(function () use ($task, $actor, $userId): void {
            $this->add->execute($task, $userId, $actor->id);

            DB::afterCommit(fn () => event(TaskAssigned::fromTask($task, $actor, $userId)));
        });

        return $task->refresh();
    }
}
