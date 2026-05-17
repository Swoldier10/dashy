<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Tasks\Models\Task;

class RemoveTaskAssigneeAction
{
    public function execute(Task $task, int $userId): void
    {
        $task->assignees()->detach($userId);
    }
}
