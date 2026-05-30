<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Tasks\Models\Task;

class TaskExistsInProjectAction
{
    public function execute(int $taskId, int $projectId): bool
    {
        return Task::query()
            ->where('id', $taskId)
            ->where('project_id', $projectId)
            ->exists();
    }
}
