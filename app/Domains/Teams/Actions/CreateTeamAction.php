<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;

class CreateTeamAction
{
    /**
     * @param  array{name: string, personal_team?: bool}  $attributes
     */
    public function execute(array $attributes): Team
    {
        $team = new Team;
        $team->forceFill([
            'name' => $attributes['name'],
            'personal_team' => (bool) ($attributes['personal_team'] ?? false),
        ]);
        $team->save();

        return $team->refresh();
    }
}
