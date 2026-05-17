<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListRecentTimeEntriesForUserAction
{
    /**
     * Recent time entries belonging to the user, newest first. `limit` caps
     * the result so the AI chat can't accidentally pull thousands of rows.
     *
     * @return Collection<int, TimeEntry>
     */
    public function execute(User $user, int $limit = 25): Collection
    {
        $limit = max(1, min(200, $limit));

        return TimeEntry::query()
            ->where('user_id', $user->id)
            ->with(['task:id,name,project_id'])
            ->latest('started_at')
            ->limit($limit)
            ->get();
    }
}
