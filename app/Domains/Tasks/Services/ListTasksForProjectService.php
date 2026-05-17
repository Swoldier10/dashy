<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Actions\ListTasksForProjectAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

final class ListTasksForProjectService
{
    public function __construct(
        private ListTasksForProjectAction $list,
    ) {}

    /**
     * @return Collection<int, Task>
     */
    public function execute(User $actor, Project $project, bool $includeArchived = false): Collection
    {
        Gate::forUser($actor)->authorize('viewAny', [Task::class, $project]);

        return $this->list->execute($project, $includeArchived);
    }
}
