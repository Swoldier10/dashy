<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;

class DeleteTeamAction
{
    public function execute(Team $team): void
    {
        $team->delete();
    }
}
