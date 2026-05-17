<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\ListTasksForUserAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Open tasks for the actor across every team they belong to. "Open" means the
 * task is not archived and not sitting in a "done" status category. Optional
 * assignee filter narrows to tasks the actor is assigned to (default: all
 * visible).
 */
final class ListUserOpenTasksService
{
    public function __construct(
        private ListTasksForUserAction $listTasks,
    ) {}

    /**
     * @return Collection<int, Task>
     */
    public function execute(User $actor, bool $onlyMine = false, int $limit = 50): Collection
    {
        $filters = [
            'statuses_not_in_category' => ['done'],
            'include_archived' => false,
            'limit' => $limit,
        ];

        if ($onlyMine) {
            $filters['assignee_user_id'] = $actor->id;
        }

        return $this->listTasks->execute($actor, $filters);
    }
}
