<?php

namespace App\Domains\Projects\Policies;

use App\Domains\Projects\Models\Project;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $project->team->members()->whereKey($user->id)->exists();
    }

    public function create(User $user, Team $team): bool
    {
        return $team->members()->whereKey($user->id)->exists();
    }

    public function update(User $user, Project $project): bool
    {
        return $this->isOwnerOfProjectsTeam($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->isOwnerOfProjectsTeam($user, $project);
    }

    public function manageStatuses(User $user, Project $project): bool
    {
        return $this->isOwnerOfProjectsTeam($user, $project);
    }

    private function isOwnerOfProjectsTeam(User $user, Project $project): bool
    {
        return $project->team->members()
            ->whereKey($user->id)
            ->wherePivot('role', TeamRole::Owner->value)
            ->exists();
    }
}
