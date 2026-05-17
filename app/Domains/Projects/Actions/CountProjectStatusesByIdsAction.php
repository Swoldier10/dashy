<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\ProjectStatus;

class CountProjectStatusesByIdsAction
{
    /**
     * @param  list<int|string>  $ids
     */
    public function execute(int $projectId, ProjectStatusCategory $category, array $ids): int
    {
        if ($ids === []) {
            return 0;
        }

        return ProjectStatus::query()
            ->whereIn('id', array_map(intval(...), $ids))
            ->where('project_id', $projectId)
            ->where('category', $category->value)
            ->count();
    }
}
