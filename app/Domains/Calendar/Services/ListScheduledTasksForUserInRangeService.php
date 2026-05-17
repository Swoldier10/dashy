<?php

namespace App\Domains\Calendar\Services;

use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ListTasksForUserInRangeService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Bridges the Calendar UI to the Tasks domain so the calendar can overlay
 * scheduled tasks as read-only items. Calls the Tasks domain's public
 * service — never reaches into its actions or models.
 */
final class ListScheduledTasksForUserInRangeService
{
    public function __construct(
        private ListTasksForUserInRangeService $listTasks,
    ) {}

    /**
     * @return Collection<int, Task>
     */
    public function execute(User $actor, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return $this->listTasks->execute($actor, $from, $to);
    }
}
