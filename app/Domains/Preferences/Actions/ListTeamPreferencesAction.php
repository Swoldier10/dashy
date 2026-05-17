<?php

namespace App\Domains\Preferences\Actions;

use App\Domains\Preferences\Models\TeamPreference;
use Illuminate\Database\Eloquent\Collection;

class ListTeamPreferencesAction
{
    /**
     * @return Collection<int, TeamPreference>
     */
    public function execute(int $teamId, ?string $keyPrefix = null): Collection
    {
        $query = TeamPreference::query()->where('team_id', $teamId);

        if ($keyPrefix !== null) {
            $query->where('key', 'like', $keyPrefix.'%');
        }

        return $query->orderBy('key')->get();
    }
}
