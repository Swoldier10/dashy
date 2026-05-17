<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Tasks\Models\Task;

class MoveTaskToStatusAction
{
    public function execute(Task $task, int $projectStatusId, int $position): Task
    {
        $task->forceFill([
            'project_status_id' => $projectStatusId,
            'position' => $position,
        ]);
        $task->save();

        return $task->refresh();
    }
}
