<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Projects\Services\FindProjectService;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ListTasksForProjectService;
use App\Models\User;
use Throwable;

/**
 * AUTO-READ. Lists tasks for a project the user can access. Optional filters
 * narrow the result; the response is capped to the most relevant 50 tasks.
 */
final class ListTasksTool implements AiTool
{
    public function __construct(
        private ListTasksForProjectService $listTasks,
        private FindProjectService $findProject,
    ) {}

    public function name(): string
    {
        return 'list_tasks';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'List tasks in one of the user\'s projects. Use this whenever you need to see the actual '
            .'tasks (names, statuses, assignees, dates) rather than guess from the workspace skeleton. '
            .'Optional filters narrow by status, assignee, and whether to include archived tasks.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'project_id' => ['type' => 'integer'],
                'status_id' => ['type' => 'integer'],
                'assignee_user_id' => ['type' => 'integer'],
                'include_archived' => ['type' => 'boolean'],
            ],
            'required' => ['project_id'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $projectId = $arguments['project_id'] ?? null;
        if (! is_int($projectId)) {
            return AiToolValidationResult::fail(['project_id is required and must be an integer.']);
        }

        $normalized = ['project_id' => $projectId];

        if (isset($arguments['status_id']) && is_int($arguments['status_id'])) {
            $normalized['status_id'] = $arguments['status_id'];
        }
        if (isset($arguments['assignee_user_id']) && is_int($arguments['assignee_user_id'])) {
            $normalized['assignee_user_id'] = $arguments['assignee_user_id'];
        }
        if (array_key_exists('include_archived', $arguments)) {
            $normalized['include_archived'] = (bool) $arguments['include_archived'];
        }

        return AiToolValidationResult::ok($normalized);
    }

    public function execute(User $user, array $arguments): array
    {
        try {
            $project = $this->findProject->execute($user, (int) $arguments['project_id']);
        } catch (Throwable) {
            return ['error' => 'Project not found.'];
        }

        $tasks = $this->listTasks->execute(
            $user,
            $project,
            (bool) ($arguments['include_archived'] ?? false),
        );

        if (isset($arguments['status_id'])) {
            $tasks = $tasks->where('project_status_id', $arguments['status_id'])->values();
        }
        if (isset($arguments['assignee_user_id'])) {
            $assigneeId = (int) $arguments['assignee_user_id'];
            $tasks = $tasks->filter(fn (Task $t) => $t->assignees->contains('id', $assigneeId))->values();
        }

        return [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'count' => $tasks->count(),
            'tasks' => $tasks->take(50)->map(fn (Task $t) => $this->slim($t))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function slim(Task $task): array
    {
        return [
            'id' => $task->id,
            'name' => (string) $task->name,
            'status_id' => $task->project_status_id,
            'status_name' => $task->status?->name,
            'priority' => $task->priority?->value,
            'start_date' => $task->start_date?->toDateString(),
            'end_date' => $task->end_date?->toDateString(),
            'is_archived' => (bool) $task->is_archived,
            'assignee_ids' => $task->assignees->pluck('id')->all(),
        ];
    }
}
