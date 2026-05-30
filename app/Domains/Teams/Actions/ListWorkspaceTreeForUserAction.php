<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListWorkspaceTreeForUserAction
{
    /**
     * The user's accessible teams eager-loaded with their members, projects,
     * and each project's statuses — the full workspace tree. Teams is the
     * tenancy root, so the team→projects→statuses hierarchy is resolved here.
     *
     * @return Collection<int, Team>
     */
    public function execute(User $user): Collection
    {
        return Team::query()
            ->whereHas('members', fn ($q) => $q->whereKey($user->id))
            ->with([
                'members:id,name',
                'projects:id,team_id,name',
                'projects.statuses:id,project_id,category,name,position',
            ])
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
