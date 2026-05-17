<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Models\TimeEntry;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class ListTimeEntriesForMonthAction
{
    /**
     * All entries whose started_at falls within the [$start, $end] window
     * and that belong to the given project. Scoping mirrors
     * SumDailyHoursForProjectAction so totals stay consistent with the
     * dashboard KPI. Eager-loads user + task so the workbook builder can
     * render rows without N+1 queries.
     *
     * @return Collection<int, TimeEntry>
     */
    public function execute(int $projectId, CarbonImmutable $start, CarbonImmutable $end, ?int $userId = null): Collection
    {
        return TimeEntry::query()
            ->whereHas('task', fn ($q) => $q->where('project_id', $projectId))
            ->whereBetween('started_at', [$start, $end])
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->with(['user:id,name', 'task:id,name,project_id'])
            ->orderBy('started_at')
            ->orderBy('id')
            ->get();
    }
}
