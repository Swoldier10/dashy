<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;

class CountTeamOwnersAction
{
    public function execute(Team $team): int
    {
        return $team->members()
            ->wherePivot('role', TeamRole::Owner->value)
            ->count();
    }
}
