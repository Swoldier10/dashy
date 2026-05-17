<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListProjectsForUserAction
{
    /**
     * @return Collection<int, Project>
     */
    public function execute(User $user): Collection
    {
        return Project::query()
            ->whereHas('team.members', fn ($q) => $q->whereKey($user->id))
            ->orderBy('team_id')
            ->orderBy('created_at')
            ->get();
    }
}
