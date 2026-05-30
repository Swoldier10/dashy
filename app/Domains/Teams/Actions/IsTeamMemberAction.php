<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;

class IsTeamMemberAction
{
    public function execute(Team $team, int $userId): bool
    {
        return $team->members()->whereKey($userId)->exists();
    }
}
