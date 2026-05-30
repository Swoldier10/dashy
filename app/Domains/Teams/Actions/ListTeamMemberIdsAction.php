<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;

class ListTeamMemberIdsAction
{
    /**
     * @return array<int, int>
     */
    public function execute(Team $team): array
    {
        return $team->members()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
    }
}
