<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;

class DeleteTeamsByIdsAction
{
    /**
     * @param  array<int, int>  $teamIds
     */
    public function execute(array $teamIds): void
    {
        Team::whereIn('id', $teamIds)->delete();
    }
}
