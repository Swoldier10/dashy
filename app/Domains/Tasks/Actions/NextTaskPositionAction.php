<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Tasks\Models\Task;

class NextTaskPositionAction
{
    public function execute(int $projectId, int $projectStatusId): int
    {
        $max = Task::query()
            ->where('project_id', $projectId)
            ->where('project_status_id', $projectStatusId)
            ->max('position');

        return $max === null ? 0 : ((int) $max) + 1;
    }
}
