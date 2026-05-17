<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\FindTeamForUserAction;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Returns the members of a team the actor belongs to. If the actor is not a
 * member, a not-found exception is thrown so we never leak team existence to
 * outsiders. Each returned User carries its `pivot.role` for the team.
 *
 * @phpstan-import-type TeamRole from \App\Domains\Teams\Enums\TeamRole
 */
final class ListTeamMembersService
{
    public function __construct(
        private FindTeamForUserAction $findTeamForUser,
    ) {}

    /**
     * @return Collection<int, User>
     */
    public function execute(User $actor, int $teamId): Collection
    {
        $team = $this->findTeamForUser->execute($actor, $teamId);
        if ($team === null) {
            throw new ModelNotFoundException;
        }

        return $team->members()->orderBy('name')->get();
    }
}
