<?php

namespace App\Domains\Teams\Actions;

use App\Models\User;

class ListSoloTeamIdsForUserAction
{
    /**
     * Ids of the teams the user belongs to that have exactly one member
     * (i.e. the user is the sole member). Resolved in a single query.
     *
     * @return array<int, int>
     */
    public function execute(User $user): array
    {
        return $user->teams()
            ->has('members', '=', 1)
            ->pluck('teams.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
