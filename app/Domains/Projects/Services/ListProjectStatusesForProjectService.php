<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\ListProjectStatusesForProjectAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

final class ListProjectStatusesForProjectService
{
    public function __construct(
        private ListProjectStatusesForProjectAction $list,
    ) {}

    /**
     * @return Collection<int, ProjectStatus>
     */
    public function execute(User $actor, Project $project): Collection
    {
        Gate::forUser($actor)->authorize('view', $project);

        return $this->list->execute($project);
    }
}
