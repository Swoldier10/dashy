<?php

namespace App\Domains\TimeTracking\Services;

use App\Domains\Projects\Actions\FindProjectAction;
use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Actions\CountDailyEntriesForProjectAction;
use App\Domains\TimeTracking\Actions\SumDailyHoursForProjectAction;
use App\Domains\TimeTracking\Actions\SumTotalHoursForProjectAction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;

class ProjectTimeStatsService
{
    public function __construct(
        private readonly SumDailyHoursForProjectAction $daily,
        private readonly CountDailyEntriesForProjectAction $dailyCount,
        private readonly SumTotalHoursForProjectAction $total,
        private readonly FindProjectAction $find,
    ) {}

    /**
     * Daily totals for the calendar month containing $monthAnchor, with
     * every day back-filled to 0 so the chart has a stable bar count.
     *
     * @return array<string, int> map of YYYY-MM-DD => seconds, in order.
     */
    public function dailyHoursForMonth(User $actor, int $projectId, CarbonImmutable $monthAnchor, ?int $userId = null): array
    {
        $project = $this->find->execute($projectId);
        Gate::forUser($actor)->authorize('viewAny', [Task::class, $project]);

        $start = $monthAnchor->startOfMonth();
        $end = $monthAnchor->endOfMonth()->endOfDay();

        $raw = $this->daily->execute($projectId, $start, $end, $userId);

        $out = [];
        $cursor = $start;
        while ($cursor->lessThanOrEqualTo($end)) {
            $key = $cursor->toDateString();
            $out[$key] = $raw[$key] ?? 0;
            $cursor = $cursor->addDay();
        }

        return $out;
    }

    /**
     * Entry counts for the calendar month containing $monthAnchor, with every
     * day back-filled to 0 so it lines up 1:1 with dailyHoursForMonth.
     *
     * @return array<string, int> map of YYYY-MM-DD => entry count, in order.
     */
    public function dailyEntryCountsForMonth(User $actor, int $projectId, CarbonImmutable $monthAnchor, ?int $userId = null): array
    {
        $project = $this->find->execute($projectId);
        Gate::forUser($actor)->authorize('viewAny', [Task::class, $project]);

        $start = $monthAnchor->startOfMonth();
        $end = $monthAnchor->endOfMonth()->endOfDay();

        $raw = $this->dailyCount->execute($projectId, $start, $end, $userId);

        $out = [];
        $cursor = $start;
        while ($cursor->lessThanOrEqualTo($end)) {
            $key = $cursor->toDateString();
            $out[$key] = $raw[$key] ?? 0;
            $cursor = $cursor->addDay();
        }

        return $out;
    }

    public function totalSecondsForProject(User $actor, int $projectId, ?int $userId = null): int
    {
        $project = $this->find->execute($projectId);
        Gate::forUser($actor)->authorize('viewAny', [Task::class, $project]);

        return $this->total->execute($projectId, $userId);
    }

    /**
     * Billing rate for the project's owning team, or null when the team has
     * not configured a rate + currency pair.
     *
     * @return array{rate: float, currency: string}|null
     */
    public function billingRateForProject(User $actor, int $projectId): ?array
    {
        $project = $this->find->execute($projectId);
        Gate::forUser($actor)->authorize('viewAny', [Task::class, $project]);

        $team = $project->team;
        if ($team === null || $team->hourly_rate === null || $team->currency === null) {
            return null;
        }

        return [
            'rate' => (float) $team->hourly_rate,
            'currency' => $team->currency->value,
        ];
    }
}
