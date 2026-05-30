<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\ListOwnedTeamsWithCountsForUserAction;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Teams that would be left ownerless if the user disappeared: those where the
 * user is the SOLE owner AND other members still depend on the team. Used to
 * block account deletion until the user transfers or deletes such teams.
 */
final class ListSoleOwnedSharedTeamsForUserService
{
    public function __construct(
        private ListOwnedTeamsWithCountsForUserAction $listOwnedTeams,
    ) {}

    /**
     * @return Collection<int, Team>
     */
    public function execute(User $user): Collection
    {
        return $this->listOwnedTeams->execute($user)
            ->filter(fn (Team $team) => (int) $team->members_count > 1 && (int) $team->owners_count === 1)
            ->values();
    }
}
