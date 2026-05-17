<?php

namespace App\Domains\Teams\Policies;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function view(User $user, Team $team): bool
    {
        return $this->isMember($user, $team);
    }

    public function update(User $user, Team $team): bool
    {
        return $this->isOwner($user, $team);
    }

    public function delete(User $user, Team $team): bool
    {
        return $this->isOwner($user, $team);
    }

    public function addMember(User $user, Team $team): bool
    {
        return $this->isOwner($user, $team);
    }

    public function removeMember(User $user, Team $team): bool
    {
        return $this->isOwner($user, $team);
    }

    private function isMember(User $user, Team $team): bool
    {
        return $team->members()->whereKey($user->id)->exists();
    }

    private function isOwner(User $user, Team $team): bool
    {
        return $team->members()
            ->whereKey($user->id)
            ->wherePivot('role', TeamRole::Owner->value)
            ->exists();
    }
}
