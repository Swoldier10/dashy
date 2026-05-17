<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteOrphanedTeamsForUserAction
{
    /**
     * Delete every team where $user is the only member. Used by
     * DeleteAccountService just before deleting the user, to clean
     * up the personal team plus any other solo teams the user owned.
     */
    public function execute(User $user): void
    {
        $teamIds = DB::table('team_user')
            ->where('user_id', $user->id)
            ->pluck('team_id')
            ->all();

        if ($teamIds === []) {
            return;
        }

        $soloTeamIds = DB::table('team_user')
            ->select('team_id')
            ->whereIn('team_id', $teamIds)
            ->groupBy('team_id')
            ->havingRaw('COUNT(*) = 1')
            ->pluck('team_id')
            ->all();

        if ($soloTeamIds === []) {
            return;
        }

        Team::whereIn('id', $soloTeamIds)->delete();
    }
}
