<?php

namespace App\Domains\TimeTracking\Policies;

use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;

class TimeEntryPolicy
{
    public function viewAny(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }

    public function create(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }

    public function update(User $user, TimeEntry $entry): bool
    {
        if ($entry->user_id === $user->id) {
            return true;
        }

        return $this->isMember($user, $entry->task);
    }

    public function delete(User $user, TimeEntry $entry): bool
    {
        return $this->update($user, $entry);
    }

    private function isMember(User $user, Task $task): bool
    {
        return $task->project->team->members()->whereKey($user->id)->exists();
    }
}
