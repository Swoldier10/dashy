<?php

namespace App\Domains\Tasks\Policies;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $this->isMember($user, $project);
    }

    public function view(User $user, Task $task): bool
    {
        return $this->isMember($user, $task->project);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->isMember($user, $project);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->isMember($user, $task->project);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->isMember($user, $task->project);
    }

    private function isMember(User $user, Project $project): bool
    {
        return $project->team->members()->whereKey($user->id)->exists();
    }
}
