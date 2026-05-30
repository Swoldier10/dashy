<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\FindTeamForUserAction;
use App\Domains\Teams\Models\Team;
use App\Models\User;

/**
 * Returns the team only if the user is a member. Returns null when the
 * user has no membership — callers (Preferences, Chat AI) branch on null
 * and must NOT be switched to authorization-exception flow.
 */
final class FindTeamForUserService
{
    public function __construct(
        private FindTeamForUserAction $find,
    ) {}

    public function execute(User $user, int $teamId): ?Team
    {
        return $this->find->execute($user, $teamId);
    }
}
