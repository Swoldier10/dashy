<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use Illuminate\Database\Eloquent\Collection;

class ListProjectStatusesForProjectAction
{
    /**
     * @return Collection<int, ProjectStatus>
     */
    public function execute(Project $project): Collection
    {
        return ProjectStatus::query()
            ->where('project_id', $project->id)
            ->orderBy('category')
            ->orderBy('position')
            ->get();
    }
}
