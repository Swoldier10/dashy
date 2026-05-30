<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\AddTaskAssigneeAction;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Services\ListTeamMemberIdsService;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Adds the same assignee to a batch of tasks atomically. Validation matches
 * AssignTaskService: each task must belong to a project on a team the
 * assignee is a member of; the actor must have update rights on every task.
 * Already-assigned tasks are skipped silently.
 */
final class BulkAssignTasksService
{
    public function __construct(
        private FindTaskAction $find,
        private AddTaskAssigneeAction $add,
        private ListTeamMemberIdsService $listTeamMemberIds,
    ) {}

    /**
     * @param  list<int>  $taskIds
     * @return Collection<int, Task>
     */
    public function execute(User $actor, array $taskIds, int $userId): Collection
    {
        $taskIds = array_values(array_unique(array_map(fn ($id) => (int) $id, $taskIds)));
        if ($taskIds === []) {
            throw ValidationException::withMessages([
                'task_ids' => __('Provide at least one task id.'),
            ]);
        }

        return DB::transaction(function () use ($actor, $taskIds, $userId): Collection {
            /** @var Collection<int, Task> $assigned */
            $assigned = new Collection;

            /** @var array<int, list<int>> $memberIdsByTeam */
            $memberIdsByTeam = [];

            foreach ($taskIds as $taskId) {
                $task = $this->find->execute($taskId);
                Gate::forUser($actor)->authorize('update', $task);

                $team = $task->project->team;
                $teamId = (int) $team->id;
                if (! array_key_exists($teamId, $memberIdsByTeam)) {
                    $memberIdsByTeam[$teamId] = $this->listTeamMemberIds->execute($team);
                }

                if (! in_array($userId, $memberIdsByTeam[$teamId], true)) {
                    throw ValidationException::withMessages([
                        'user_id' => __('The selected user is not a member of every target team.'),
                    ]);
                }

                if ($task->assignees->contains('id', $userId)) {
                    $assigned->push($task);

                    continue;
                }

                $this->add->execute($task, $userId, $actor->id);
                $assigned->push($task->refresh());
            }

            return $assigned;
        });
    }
}
