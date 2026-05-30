<?php

namespace App\Domains\Teams\Actions;

use App\Models\User;

class ListTeamIdsForUserAction
{
    /**
     * @return array<int, int>
     */
    public function execute(User $user): array
    {
        return $user->teams()->pluck('teams.id')->map(fn ($id) => (int) $id)->all();
    }
}
