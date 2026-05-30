<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;
use App\Models\User;

class FindPersonalTeamForUserAction
{
    public function execute(User $user): ?Team
    {
        /** @var Team|null $team */
        $team = $user->teams()
            ->where('teams.personal_team', true)
            ->first();

        return $team;
    }
}
