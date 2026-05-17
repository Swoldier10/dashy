<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Models\TimeEntry;
use Illuminate\Support\Carbon;

class SumDurationForTaskAction
{
    public function execute(Task $task): int
    {
        $closed = (int) TimeEntry::query()
            ->where('task_id', $task->id)
            ->whereNotNull('ended_at')
            ->sum('duration_seconds');

        $running = TimeEntry::query()
            ->where('task_id', $task->id)
            ->whereNull('ended_at')
            ->get(['started_at']);

        $now = Carbon::now();
        foreach ($running as $entry) {
            $closed += max(0, $now->diffInSeconds($entry->started_at, true));
        }

        return $closed;
    }
}
