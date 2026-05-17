<?php

namespace App\Domains\Preferences\Actions;

use App\Domains\Preferences\Models\TeamPreference;

class DeleteTeamPreferenceAction
{
    public function execute(int $teamId, string $key): int
    {
        return TeamPreference::query()
            ->where('team_id', $teamId)
            ->where('key', $key)
            ->delete();
    }
}
