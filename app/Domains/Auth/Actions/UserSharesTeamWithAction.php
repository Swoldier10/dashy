<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;

class UserSharesTeamWithAction
{
    /**
     * Returns true if $candidate shares at least one team with $actor.
     * Used to gate "who is X?" lookups so an unrelated user can't be used
     * as an existence oracle.
     */
    public function execute(User $candidate, User $actor): bool
    {
        return User::query()
            ->whereKey($candidate->id)
            ->whereHas('teams', fn ($q) => $q->whereHas('members', fn ($m) => $m->whereKey($actor->id)))
            ->exists();
    }
}
