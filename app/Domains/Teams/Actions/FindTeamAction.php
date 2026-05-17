<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;

class FindTeamAction
{
    public function execute(int $id): Team
    {
        return Team::query()->findOrFail($id);
    }
}
