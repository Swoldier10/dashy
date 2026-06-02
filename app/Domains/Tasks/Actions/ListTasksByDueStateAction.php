<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Tasks\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Cross-user due-state scan for the notifications scheduler. Deliberately
 * NOT scoped to an actor (unlike ListTasksForUserAction) — the scheduler
 * reminds every assignee of every team. Excludes archived tasks and tasks
 * in done/closed categories.
 */
class ListTasksByDueStateAction
{
    /**
     * @return Collection<int, Task>
     */
    public function execute(string $mode, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return Task::query()
            ->with(['assignees', 'project'])
            ->where('is_archived', false)
            ->whereNotNull('end_date')
            ->when(
                $mode === 'overdue',
                fn ($query) => $query->where('end_date', '<', $to),
                fn ($query) => $query->whereBetween('end_date', [$from, $to]),
            )
            ->whereHas('status', fn ($q) => $q->whereNotIn('category', [
                ProjectStatusCategory::Done->value,
                ProjectStatusCategory::Closed->value,
            ]))
            ->orderBy('end_date')
            ->get();
    }
}
