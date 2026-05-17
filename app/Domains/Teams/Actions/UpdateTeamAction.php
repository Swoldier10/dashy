<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;

class UpdateTeamAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Team $team, array $attributes): Team
    {
        $team->forceFill($attributes);
        $team->save();

        return $team->refresh();
    }
}
