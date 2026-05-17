<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\ProjectStatus;

class ReorderProjectStatusesAction
{
    /**
     * @param  list<int|string>  $orderedIds
     */
    public function execute(int $projectId, ProjectStatusCategory $category, array $orderedIds): void
    {
        foreach (array_values($orderedIds) as $position => $id) {
            ProjectStatus::query()
                ->where('id', (int) $id)
                ->where('project_id', $projectId)
                ->where('category', $category->value)
                ->update(['position' => $position]);
        }
    }
}
