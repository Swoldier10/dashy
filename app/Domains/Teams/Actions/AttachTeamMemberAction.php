<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;

class AttachTeamMemberAction
{
    public function execute(Team $team, User $user, TeamRole $role): void
    {
        $team->members()->attach($user->id, ['role' => $role->value]);
    }
}
