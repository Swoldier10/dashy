<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;

class FindActiveTimerForUserAction
{
    public function execute(User $user): ?TimeEntry
    {
        return TimeEntry::query()
            ->with('task.project')
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();
    }
}
