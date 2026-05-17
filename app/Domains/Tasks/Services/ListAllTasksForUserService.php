<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\ListAllTasksForUserAction;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class ListAllTasksForUserService
{
    public function __construct(
        private ListAllTasksForUserAction $list,
    ) {}

    /**
     * @return Collection<int, Task>
     */
    public function execute(User $actor, ?Team $team = null, bool $includeArchived = false): Collection
    {
        return $this->list->execute($actor, $team, $includeArchived);
    }
}
