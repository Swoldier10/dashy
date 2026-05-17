<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\BulkMoveTasksService;
use App\Models\User;

/**
 * CONFIRM-WRITE (bulk). Moves a batch of tasks to a single target status
 * atomically. All tasks must share the target status's project.
 */
final class BulkMoveTasksToStatusTool implements AiTool
{
    public function __construct(
        private BulkMoveTasksService $bulkMove,
    ) {}

    public function name(): string
    {
        return 'bulk_move_tasks_to_status';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Move multiple tasks to the same target status, in one atomic operation. All listed '
            .'tasks must belong to the target status\'s project. Prefer this over emitting many '
            .'`move_task_to_status` calls.';
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
                'target_status_id' => ['type' => 'integer'],
            ],
            'required' => ['task_ids', 'target_status_id'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $taskIds = $arguments['task_ids'] ?? null;
        $targetStatusId = $arguments['target_status_id'] ?? null;

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
        if (! is_int($targetStatusId)) {
            return AiToolValidationResult::fail(['target_status_id is required and must be an integer.']);
        }

        return AiToolValidationResult::ok([
            'task_ids' => $normalizedIds,
            'target_status_id' => $targetStatusId,
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $moved = $this->bulkMove->execute(
            $user,
            (array) $arguments['task_ids'],
            (int) $arguments['target_status_id'],
        );

        return [
            'count' => $moved->count(),
            'target_status_id' => (int) $arguments['target_status_id'],
            'task_ids' => $moved->pluck('id')->all(),
        ];
    }
}
