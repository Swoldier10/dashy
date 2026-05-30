<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListOwnedTeamsWithCountsForUserAction
{
    /**
     * Teams the user owns, each hydrated with `members_count` and
     * `owners_count`, resolved in a single query (no per-team lookups).
     *
     * @return Collection<int, Team>
     */
    public function execute(User $user): Collection
    {
        return $user->teams()
            ->wherePivot('role', TeamRole::Owner->value)
            ->withCount(['members', 'owners'])
            ->get();
    }
}
