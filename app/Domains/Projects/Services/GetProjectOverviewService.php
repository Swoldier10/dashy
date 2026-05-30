<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\FindProjectAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Services\ListTasksForProjectService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Returns a project plus a lightweight snapshot of its current state — total
 * task count, counts grouped by status, and the statuses themselves. Intended
 * as the workhorse behind the AI chat's `get_project_overview` tool. The
 * actor must be a member of the project's team or a 404-shaped exception is
 * thrown (so we never leak project existence to non-members).
 */
final class GetProjectOverviewService
{
    public function __construct(
        private FindProjectAction $findProject,
        private ListTasksForProjectService $listTasks,
    ) {}

    /**
     * @return array{project: Project, total_tasks: int, open_tasks: int, by_status: array<int, array{status_id: int, status_name: string, category: string, task_count: int}>}
     */
    public function execute(User $actor, int $projectId): array
    {
        $project = $this->findProject->execute($projectId);

        // Reshape the AuthorizationException into the 404-shaped error the
        // AI chat tool relies on to avoid leaking project existence.
        try {
            $tasks = $this->listTasks->execute($actor, $project, includeArchived: false);
        } catch (AuthorizationException) {
            throw new ModelNotFoundException;
        }

        $byStatus = $project->statuses->mapWithKeys(fn ($status) => [
            $status->id => [
                'status_id' => $status->id,
                'status_name' => (string) $status->name,
                'category' => $status->category->value,
                'task_count' => 0,
            ],
        ])->all();

        foreach ($tasks as $task) {
            if (isset($byStatus[$task->project_status_id])) {
                $byStatus[$task->project_status_id]['task_count']++;
            }
        }

        $openCount = $tasks->filter(
            fn ($t) => $t->status !== null && $t->status->category->value !== 'done',
        )->count();

        return [
            'project' => $project,
            'total_tasks' => $tasks->count(),
            'open_tasks' => $openCount,
            'by_status' => array_values($byStatus),
        ];
    }
}
