<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;
use App\Models\User;

class FindTeamMemberByIdAction
{
    public function execute(Team $team, int $memberId): ?User
    {
        /** @var User|null $member */
        $member = $team->members()->whereKey($memberId)->first();

        return $member;
    }
}
