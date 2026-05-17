<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Models\TimeEntry;
use Illuminate\Support\Carbon;

class SumTotalHoursForProjectAction
{
    /**
     * Total tracked seconds for the project. Includes elapsed time of any
     * currently-running timers so a freshly-started timer is reflected
     * immediately (matches SumDurationForTaskAction).
     */
    public function execute(int $projectId, ?int $userId = null): int
    {
        $closed = (int) TimeEntry::query()
            ->whereHas('task', fn ($q) => $q->where('project_id', $projectId))
            ->whereNotNull('ended_at')
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->sum('duration_seconds');

        $running = TimeEntry::query()
            ->whereHas('task', fn ($q) => $q->where('project_id', $projectId))
            ->whereNull('ended_at')
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->get(['started_at']);

        $now = Carbon::now();
        foreach ($running as $entry) {
            $closed += max(0, $now->diffInSeconds($entry->started_at, true));
        }

        return $closed;
    }
}
