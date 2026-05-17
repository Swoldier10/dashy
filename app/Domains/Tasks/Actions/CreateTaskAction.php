<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Tasks\Models\Task;

class CreateTaskAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Task
    {
        $task = new Task;
        $task->forceFill([
            'project_id' => $attributes['project_id'],
            'project_status_id' => $attributes['project_status_id'],
            'created_by_user_id' => $attributes['created_by_user_id'] ?? null,
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'priority' => $attributes['priority'] ?? 'normal',
            'start_date' => $attributes['start_date'] ?? null,
            'end_date' => $attributes['end_date'] ?? null,
            'position' => $attributes['position'] ?? 0,
            'attachments' => $attributes['attachments'] ?? null,
        ]);
        $task->save();

        return $task->refresh();
    }
}
