<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListAllTasksForUserAction
{
    /**
     * Tasks across every project the user can view, optionally scoped to a
     * single team. Eager-loads the joins needed by the aggregator view so the
     * Volt component never re-queries.
     *
     * @return Collection<int, Task>
     */
    public function execute(User $user, ?Team $team = null, bool $includeArchived = false): Collection
    {
        return Task::query()
            ->whereHas('project.team.members', fn ($q) => $q->whereKey($user->id))
            ->when(
                $team,
                fn ($q) => $q->whereHas('project', fn ($p) => $p->where('team_id', $team->id))
            )
            ->when(! $includeArchived, fn ($q) => $q->where('is_archived', false))
            ->with(['project.team', 'status', 'assignees'])
            ->withSum(['timeEntries as total_tracked_seconds' => function ($query) {
                $query->whereNotNull('ended_at');
            }], 'duration_seconds')
            ->orderBy('project_status_id')
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }
}
