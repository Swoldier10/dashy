<?php

namespace App\Domains\Chat\Ai\Actions;

use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class LoadAiWorkspaceContextAction
{
    /**
     * Returns the user's accessible teams eager-loaded with their members,
     * projects, and each project's statuses — everything needed to build
     * the AI context payload.
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
