<?php

namespace App\Domains\Preferences\Actions;

use App\Domains\Preferences\Models\TeamPreference;

class UpsertTeamPreferenceAction
{
    public function execute(int $teamId, string $key, mixed $value): TeamPreference
    {
        return TeamPreference::updateOrCreate(
            ['team_id' => $teamId, 'key' => $key],
            ['value' => $value],
        );
    }
}
