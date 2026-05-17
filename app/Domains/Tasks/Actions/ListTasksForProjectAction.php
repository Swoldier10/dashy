<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class ListTasksForProjectAction
{
    /**
     * @return Collection<int, Task>
     */
    public function execute(Project $project, bool $includeArchived = false): Collection
    {
        return Task::query()
            ->where('project_id', $project->id)
            ->when(! $includeArchived, fn ($q) => $q->where('is_archived', false))
            ->with(['assignees', 'creator'])
            ->withSum(['timeEntries as total_tracked_seconds' => function ($query) {
                $query->whereNotNull('ended_at');
            }], 'duration_seconds')
            ->orderBy('project_status_id')
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }
}
