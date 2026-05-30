<?php

namespace App\Domains\TimeTracking\Services;

use App\Domains\Projects\Services\FindProjectService;
use App\Domains\TimeTracking\Actions\ListTimeEntriesForMonthAction;
use App\Domains\TimeTracking\DTOs\MonthlyTimeEntriesExport;
use App\Domains\TimeTracking\Support\MonthlyTimeEntriesWorkbook;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExportMonthTimeEntriesService
{
    public function __construct(
        private readonly ListTimeEntriesForMonthAction $list,
        private readonly FindProjectService $find,
    ) {}

    public function execute(User $actor, int $projectId, CarbonImmutable $monthAnchor, ?int $userId): MonthlyTimeEntriesExport
    {
        $project = $this->find->execute($actor, $projectId);

        $start = $monthAnchor->startOfMonth();
        $end = $monthAnchor->endOfMonth()->endOfDay();

        return DB::transaction(function () use ($project, $start, $end, $userId) {
            $entries = $this->list->execute($project->id, $start, $end, $userId);

            $scopeLabel = $userId === null
                ? __('Team-Einträge')
                : __('Eigene Einträge');

            $bytes = MonthlyTimeEntriesWorkbook::build($project, $start, $scopeLabel, $entries);

            $filename = sprintf(
                'zeiteintraege-%s-%s.xlsx',
                Str::slug($project->name),
                $start->format('Y-m'),
            );

            return new MonthlyTimeEntriesExport($filename, $bytes);
        });
    }
}
