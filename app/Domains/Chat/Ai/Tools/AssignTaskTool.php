<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\AssignTaskService;
use App\Models\User;

/**
 * CONFIRM-WRITE. Adds a team member as an assignee on a task. Idempotent on
 * the service level — already-assigned users are skipped without error.
 */
final class AssignTaskTool implements AiTool
{
    public function __construct(
        private AssignTaskService $assignTask,
    ) {}

    public function name(): string
    {
        return 'assign_task';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Add a user as an assignee to a task. The user must be a member of the task\'s team. '
            .'Resolve a name to a user_id via list_team_members or find_user_by_email first.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_id' => ['type' => 'integer'],
                'user_id' => ['type' => 'integer'],
            ],
            'required' => ['task_id', 'user_id'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $taskId = $arguments['task_id'] ?? null;
        $userId = $arguments['user_id'] ?? null;

        if (! is_int($taskId)) {
            return AiToolValidationResult::fail(['task_id is required and must be an integer.']);
        }
        if (! is_int($userId)) {
            return AiToolValidationResult::fail(['user_id is required and must be an integer.']);
        }

        return AiToolValidationResult::ok([
            'task_id' => $taskId,
            'user_id' => $userId,
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $task = $this->assignTask->execute(
            $user,
            (int) $arguments['task_id'],
            (int) $arguments['user_id'],
        );

        return [
            'task_id' => $task->id,
            'assignee_ids' => $task->assignees->pluck('id')->all(),
        ];
    }
}
