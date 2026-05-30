<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;

class IsTeamOwnerAction
{
    public function execute(Team $team, int $userId): bool
    {
        return $team->members()
            ->whereKey($userId)
            ->wherePivot('role', TeamRole::Owner->value)
            ->exists();
    }
}
