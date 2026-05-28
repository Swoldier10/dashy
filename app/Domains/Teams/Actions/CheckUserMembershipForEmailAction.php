<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;

class CheckUserMembershipForEmailAction
{
    public function execute(Team $team, string $email): bool
    {
        return $team->members()
            ->where('users.email', $email)
            ->exists();
    }
}
