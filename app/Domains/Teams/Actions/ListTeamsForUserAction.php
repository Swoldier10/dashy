<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListTeamsForUserAction
{
    /**
     * @return Collection<int, Team>
     */
    public function execute(User $user): Collection
    {
        return $user->teams()
            ->withCount('members')
            ->orderBy('teams.personal_team', 'desc')
            ->orderBy('teams.created_at')
            ->get();
    }
}
