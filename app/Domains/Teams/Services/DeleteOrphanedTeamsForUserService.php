<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\DeleteTeamsByIdsAction;
use App\Domains\Teams\Actions\ListSoloTeamIdsForUserAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Deletes every team where $user is the only member. Called from
 * DeleteAccountService just before deleting the user account.
 */
final class DeleteOrphanedTeamsForUserService
{
    public function __construct(
        private ListSoloTeamIdsForUserAction $listSoloTeamIds,
        private DeleteTeamsByIdsAction $deleteTeams,
    ) {}

    public function execute(User $user): void
    {
        $soloTeamIds = $this->listSoloTeamIds->execute($user);

        if ($soloTeamIds === []) {
            return;
        }

        DB::transaction(fn () => $this->deleteTeams->execute($soloTeamIds));
    }
}
