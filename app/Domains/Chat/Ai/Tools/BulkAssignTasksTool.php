<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\BulkAssignTasksService;
use App\Models\User;

/**
 * CONFIRM-WRITE (bulk). Adds the same assignee to a batch of tasks
 * atomically. The user must be a member of every target task's team.
 */
final class BulkAssignTasksTool implements AiTool
{
    public function __construct(
        private BulkAssignTasksService $bulkAssign,
    ) {}

    public function name(): string
    {
        return 'bulk_assign_tasks';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Add the same user as an assignee to a batch of tasks, in one atomic operation. The '
            .'user must already be a member of every team that owns one of the target tasks.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_ids' => [
                    'type' => 'array',
                    'items' => ['type' => 'integer'],
                    'minItems' => 1,
                ],
                'user_id' => ['type' => 'integer'],
            ],
            'required' => ['task_ids', 'user_id'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $taskIds = $arguments['task_ids'] ?? null;
        $userId = $arguments['user_id'] ?? null;

        if (! is_array($taskIds) || $taskIds === []) {
            return AiToolValidationResult::fail(['task_ids must be a non-empty array of integers.']);
        }
        $normalizedIds = array_values(array_unique(array_filter(array_map(
            fn ($v) => is_int($v) ? $v : null,
            $taskIds,
        ), fn ($v) => $v !== null)));
        if ($normalizedIds === []) {
            return AiToolValidationResult::fail(['task_ids must contain at least one integer.']);
        }
        if (! is_int($userId)) {
            return AiToolValidationResult::fail(['user_id is required and must be an integer.']);
        }

        return AiToolValidationResult::ok([
            'task_ids' => $normalizedIds,
            'user_id' => $userId,
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $assigned = $this->bulkAssign->execute(
            $user,
            (array) $arguments['task_ids'],
            (int) $arguments['user_id'],
        );

        return [
            'count' => $assigned->count(),
            'user_id' => (int) $arguments['user_id'],
            'task_ids' => $assigned->pluck('id')->all(),
        ];
    }
}
