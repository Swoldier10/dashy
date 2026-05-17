<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\BulkDeleteTasksService;
use App\Models\User;

/**
 * CONFIRM-WRITE (destructive bulk). Hard-deletes a batch of tasks atomically.
 * The card lists every task name so the user reviews exactly what disappears.
 */
final class BulkDeleteTasksTool implements AiTool
{
    public function __construct(
        private BulkDeleteTasksService $bulkDelete,
    ) {}

    public function name(): string
    {
        return 'bulk_delete_tasks';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Hard-delete a batch of tasks in one atomic operation. The card lists every task name '
            .'so the user can review before applying. Cannot be undone — prefer bulk_archive_tasks '
            .'unless the user explicitly says delete.';
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
            ],
            'required' => ['task_ids'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $taskIds = $arguments['task_ids'] ?? null;
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

        return AiToolValidationResult::ok(['task_ids' => $normalizedIds]);
    }

    public function execute(User $user, array $arguments): array
    {
        $count = $this->bulkDelete->execute($user, (array) $arguments['task_ids']);

        return [
            'count' => $count,
            'task_ids' => (array) $arguments['task_ids'],
        ];
    }
}
