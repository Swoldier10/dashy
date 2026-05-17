<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;

class FindTeamForUserAction
{
    /**
     * Return the team if $user is a member, with members eager-loaded
     * (Owners first, then alphabetical by name). Returns null if the
     * user is not a member of the team.
     */
    public function execute(User $user, int $teamId): ?Team
    {
        $team = Team::query()
            ->whereHas('members', fn ($q) => $q->whereKey($user->id))
            ->with(['members' => function ($query) {
                $query
                    ->orderByRaw('CASE team_user.role WHEN ? THEN 0 ELSE 1 END', [TeamRole::Owner->value])
                    ->orderBy('users.name');
            }])
            ->find($teamId);

        return $team;
    }
}
