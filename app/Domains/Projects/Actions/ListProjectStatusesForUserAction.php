<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListProjectStatusesForUserAction
{
    /**
     * Every status definition across every project the user can view —
     * optionally narrowed to a single team. Feeds the Workspace aggregator
     * so empty-bucket sections still render with their canonical color.
     *
     * @return Collection<int, ProjectStatus>
     */
    public function execute(User $user, ?Team $team = null): Collection
    {
        return ProjectStatus::query()
            ->whereHas('project.team.members', fn ($q) => $q->whereKey($user->id))
            ->when(
                $team,
                fn ($q) => $q->whereHas('project', fn ($p) => $p->where('team_id', $team->id))
            )
            ->with('project:id,team_id')
            ->orderBy('project_id')
            ->orderBy('category')
            ->orderBy('position')
            ->get();
    }
}
