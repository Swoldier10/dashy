<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\ListTasksForUserAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Overdue, still-open tasks across every team the actor belongs to. A task is
 * overdue when its `end_date` is strictly before today AND its status category
 * is not "done". Optional assignee filter narrows to the actor.
 */
final class ListOverdueTasksService
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
            'overdue_only' => true,
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
