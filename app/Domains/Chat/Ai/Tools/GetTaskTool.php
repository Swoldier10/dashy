<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\FindTaskService;
use App\Models\User;
use Throwable;

/**
 * AUTO-READ. Returns the full details of a single task — name, description,
 * status, assignees, dates, priority, archived flag, attachments. Authorises
 * via the task's project team membership.
 */
final class GetTaskTool implements AiTool
{
    public function __construct(
        private FindTaskService $findTask,
    ) {}

    public function name(): string
    {
        return 'get_task';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'Fetch the full record of a single task by id, including its description, assignees, dates, '
            .'priority, status, and whether it is archived. Prefer this over guessing task details from '
            .'context.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_id' => ['type' => 'integer'],
            ],
            'required' => ['task_id'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $id = $arguments['task_id'] ?? null;
        if (! is_int($id)) {
            return AiToolValidationResult::fail(['task_id is required and must be an integer.']);
        }

        return AiToolValidationResult::ok(['task_id' => $id]);
    }

    public function execute(User $user, array $arguments): array
    {
        try {
            $task = $this->findTask->execute($user, (int) $arguments['task_id']);
        } catch (Throwable $e) {
            return ['error' => 'Task not found or not accessible.'];
        }

        $task->loadMissing(['project:id,name,team_id', 'status:id,name,category', 'assignees:id,name']);

        return [
            'id' => $task->id,
            'name' => (string) $task->name,
            'description' => (string) $task->description,
            'project' => [
                'id' => $task->project?->id,
                'name' => $task->project?->name,
                'team_id' => $task->project?->team_id,
            ],
            'status' => [
                'id' => $task->status?->id,
                'name' => $task->status?->name,
                'category' => $task->status?->category->value,
            ],
            'priority' => $task->priority?->value,
            'start_date' => $task->start_date?->toDateString(),
            'end_date' => $task->end_date?->toDateString(),
            'is_archived' => (bool) $task->is_archived,
            'assignees' => $task->assignees->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->all(),
            'attachment_count' => is_array($task->attachments) ? count($task->attachments) : 0,
        ];
    }
}
