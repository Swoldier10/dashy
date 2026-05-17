<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Models\TimeEntry;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

class SumDailyHoursForProjectAction
{
    /**
     * Bucket time-entry seconds for a project by calendar day (in the app
     * timezone). Running entries (ended_at is null) contribute the elapsed
     * time since started_at — matching SumDurationForTaskAction.
     *
     * @return array<string, int> map of YYYY-MM-DD => seconds. Days with no
     *                            entries are absent; back-filling is the
     *                            service's responsibility.
     */
    public function execute(int $projectId, CarbonImmutable $start, CarbonImmutable $end, ?int $userId = null): array
    {
        $entries = TimeEntry::query()
            ->whereHas('task', fn ($q) => $q->where('project_id', $projectId))
            ->whereBetween('started_at', [$start, $end])
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->get(['started_at', 'ended_at', 'duration_seconds']);

        $timezone = config('app.timezone');
        $now = Carbon::now();
        $buckets = [];

        foreach ($entries as $entry) {
            $day = $entry->started_at->copy()->setTimezone($timezone)->toDateString();

            $seconds = $entry->ended_at === null
                ? max(0, $now->diffInSeconds($entry->started_at, true))
                : (int) $entry->duration_seconds;

            $buckets[$day] = ($buckets[$day] ?? 0) + (int) $seconds;
        }

        return $buckets;
    }
}
