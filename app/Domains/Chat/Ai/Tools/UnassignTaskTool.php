<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\UnassignTaskService;
use App\Models\User;

/**
 * CONFIRM-WRITE. Removes a single assignee from a task.
 */
final class UnassignTaskTool implements AiTool
{
    public function __construct(
        private UnassignTaskService $unassignTask,
    ) {}

    public function name(): string
    {
        return 'unassign_task';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Remove a user from a task\'s assignees.';
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
        $task = $this->unassignTask->execute(
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
