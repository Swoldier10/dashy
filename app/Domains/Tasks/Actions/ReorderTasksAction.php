<?php

namespace App\Domains\Tasks\Actions;

use App\Domains\Tasks\Models\Task;

class ReorderTasksAction
{
    /**
     * Rewrites the position column for tasks in (projectId, projectStatusId)
     * matching the given orderedIds. Tasks whose ID is not in that set are
     * left untouched, and forged IDs from another project / status are
     * silently filtered out by the WHERE clauses.
     *
     * @param  list<int|string>  $orderedIds
     */
    public function execute(int $projectId, int $projectStatusId, array $orderedIds): void
    {
        foreach (array_values($orderedIds) as $position => $id) {
            Task::query()
                ->where('id', (int) $id)
                ->where('project_id', $projectId)
                ->where('project_status_id', $projectStatusId)
                ->update(['position' => $position]);
        }
    }
}
