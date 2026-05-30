<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\ProjectStatus;

class IsProjectStatusInProjectAction
{
    public function execute(int $statusId, int $projectId): bool
    {
        return ProjectStatus::query()
            ->where('id', $statusId)
            ->where('project_id', $projectId)
            ->exists();
    }
}
