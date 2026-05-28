<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;

class FindActiveInvitationForTeamEmailAction
{
    public function execute(Team $team, string $email): ?TeamInvitation
    {
        return TeamInvitation::query()
            ->where('team_id', $team->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();
    }
}
