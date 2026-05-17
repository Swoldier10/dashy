<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;
use App\Models\User;

class DetachTeamMemberAction
{
    public function execute(Team $team, User $user): void
    {
        $team->members()->detach($user->id);
    }
}
