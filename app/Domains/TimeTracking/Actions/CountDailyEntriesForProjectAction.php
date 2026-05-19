<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Models\TimeEntry;
use Carbon\CarbonImmutable;

class CountDailyEntriesForProjectAction
{
    /**
     * Count time-entry rows for a project, bucketed by the calendar day they
     * were started on (in the app timezone). Mirrors the windowing of
     * {@see SumDailyHoursForProjectAction} so the chart's "entries per day"
     * lines up with the seconds it draws.
     *
     * Bucketing happens in PHP rather than the database so this works across
     * drivers (SQLite locally, MySQL in production) — same pattern as the
     * companion sum action.
     *
     * @return array<string, int> map of YYYY-MM-DD => entry count. Days with
     *                            zero entries are absent; the service
     *                            back-fills them.
     */
    public function execute(int $projectId, CarbonImmutable $start, CarbonImmutable $end, ?int $userId = null): array
    {
        $entries = TimeEntry::query()
            ->whereHas('task', fn ($q) => $q->where('project_id', $projectId))
            ->whereBetween('started_at', [$start, $end])
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->get(['started_at']);

        $timezone = config('app.timezone');
        $buckets = [];

        foreach ($entries as $entry) {
            $day = $entry->started_at->copy()->setTimezone($timezone)->toDateString();
            $buckets[$day] = ($buckets[$day] ?? 0) + 1;
        }

        return $buckets;
    }
}
