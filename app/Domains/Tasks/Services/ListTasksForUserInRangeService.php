<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\ListTasksForUserAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Public tasks-domain entry point for "tasks scheduled in a given date range
 * for a user". The Calendar domain calls this through its own wrapper service
 * (cross-domain rule).
 */
final class ListTasksForUserInRangeService
{
    public function __construct(
        private ListTasksForUserAction $listTasks,
    ) {}

    /**
     * @return Collection<int, Task>
     */
    public function execute(
        User $actor,
        CarbonImmutable $from,
        CarbonImmutable $to,
        bool $onlyMine = false,
    ): Collection {
        $filters = [
            'include_archived' => false,
            'range_from' => $from->toDateString(),
            'range_to' => $to->toDateString(),
        ];

        if ($onlyMine) {
            $filters['assignee_user_id'] = $actor->id;
        }

        return $this->listTasks->execute($actor, $filters);
    }
}
