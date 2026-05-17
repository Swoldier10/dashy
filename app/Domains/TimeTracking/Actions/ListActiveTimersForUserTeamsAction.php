<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListActiveTimersForUserTeamsAction
{
    /**
     * Running timers (ended_at IS NULL) for any user that shares a team with
     * the actor. Each entry comes preloaded with the timer's owner and the
     * task it's running against.
     *
     * @return Collection<int, TimeEntry>
     */
    public function execute(User $actor): Collection
    {
        return TimeEntry::query()
            ->whereNull('ended_at')
            ->with(['user:id,name', 'task:id,name,project_id'])
            ->whereHas('task.project.team.members', fn ($q) => $q->whereKey($actor->id))
            ->orderBy('started_at')
            ->get();
    }
}
