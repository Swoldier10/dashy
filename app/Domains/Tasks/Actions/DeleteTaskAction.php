<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Tasks\Models\Task;

class DeleteTaskAction
{
    public function execute(Task $task): void
    {
        $task->delete();
    }
}
