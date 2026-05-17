<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Models\TimeEntry;
use Illuminate\Database\Eloquent\Collection;

class ListTimeEntriesForTaskAction
{
    /**
     * @return Collection<int, TimeEntry>
     */
    public function execute(Task $task): Collection
    {
        return TimeEntry::query()
            ->where('task_id', $task->id)
            ->with('user')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->get();
    }
}
