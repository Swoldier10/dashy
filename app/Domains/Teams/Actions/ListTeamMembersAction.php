<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListTeamMembersAction
{
    /**
     * @return Collection<int, User>
     */
    public function execute(Team $team): Collection
    {
        return $team->members()->orderBy('name')->get();
    }
}
