<?php

namespace App\Domains\Preferences\Actions;

use App\Domains\Preferences\Models\TeamPreference;

class UpsertTeamPreferenceAction
{
    public function execute(int $teamId, string $key, mixed $value): TeamPreference
    {
        $pref = TeamPreference::query()
            ->where('team_id', $teamId)
            ->where('key', $key)
            ->first();

        if ($pref !== null) {
            $pref->forceFill(['value' => $value])->save();

            return $pref;
        }

        return TeamPreference::create([
            'team_id' => $teamId,
            'key' => $key,
            'value' => $value,
        ]);
    }
}
