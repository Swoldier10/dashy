<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Tasks\Models\Task;
use Illuminate\Support\Facades\DB;

class RemoveMemberAssignmentsForTeamAction
{
    /**
     * Detach a user from every task assignment within the given team's
     * projects. Touches only the `task_user` pivot — time entries are
     * deliberately left intact (they are retained work/billing records).
     *
     * @return int number of assignments removed
     */
    public function execute(int $teamId, int $userId): int
    {
        $taskIds = Task::query()
            ->whereHas('project', fn ($query) => $query->where('team_id', $teamId))
            ->pluck('id');

        if ($taskIds->isEmpty()) {
            return 0;
        }

        return (int) DB::table('task_user')
            ->where('user_id', $userId)
            ->whereIn('task_id', $taskIds)
            ->delete();
    }
}
