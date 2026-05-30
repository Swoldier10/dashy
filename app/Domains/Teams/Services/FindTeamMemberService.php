<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\FindTeamMemberByIdAction;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Resolves a member of a team by user ID, returning null when the candidate
 * is not part of the team. Used by the team-show page to look up the target
 * of a remove-member action without exposing arbitrary user existence.
 */
final class FindTeamMemberService
{
    public function __construct(
        private FindTeamMemberByIdAction $findMember,
    ) {}

    public function execute(User $actor, Team $team, int $memberId): ?User
    {
        Gate::forUser($actor)->authorize('view', $team);

        return $this->findMember->execute($team, $memberId);
    }
}
