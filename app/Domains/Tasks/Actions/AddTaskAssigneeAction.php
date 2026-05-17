<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Tasks\Models\Task;

class AddTaskAssigneeAction
{
    public function execute(Task $task, int $userId, ?int $assignedByUserId = null): void
    {
        $task->assignees()->syncWithoutDetaching([
            $userId => ['assigned_by_user_id' => $assignedByUserId],
        ]);
    }
}
