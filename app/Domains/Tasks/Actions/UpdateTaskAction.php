<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Tasks\Models\Task;

class UpdateTaskAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Task $task, array $attributes): Task
    {
        $task->fill($attributes);
        $task->save();

        return $task->refresh();
    }
}
