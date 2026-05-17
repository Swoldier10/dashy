<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\ProjectStatus;

class NextStatusPositionAction
{
    public function execute(int $projectId, ProjectStatusCategory $category): int
    {
        $max = ProjectStatus::query()
            ->where('project_id', $projectId)
            ->where('category', $category->value)
            ->max('position');

        return $max === null ? 0 : ((int) $max) + 1;
    }
}
