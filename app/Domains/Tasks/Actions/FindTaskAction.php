<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Tasks\Models\Task;

class FindTaskAction
{
    public function execute(int $id): Task
    {
        return Task::query()
            ->with(['status', 'assignees', 'creator', 'project'])
            ->findOrFail($id);
    }
}
