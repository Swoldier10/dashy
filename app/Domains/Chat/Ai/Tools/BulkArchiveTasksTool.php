<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\BulkArchiveTasksService;
use App\Models\User;

/**
 * CONFIRM-WRITE (bulk). Archives a batch of tasks atomically. Reversible via
 * unarchive_task once any one is restored — but the batch operation itself
 * archives all-or-nothing.
 */
final class BulkArchiveTasksTool implements AiTool
{
    public function __construct(
        private BulkArchiveTasksService $bulkArchive,
    ) {}

    public function name(): string
    {
        return 'bulk_archive_tasks';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Archive a batch of tasks in one atomic operation. Each task can be individually '
            .'restored later via unarchive_task.';
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
        $archived = $this->bulkArchive->execute($user, (array) $arguments['task_ids']);

        return [
            'count' => $archived->count(),
            'task_ids' => $archived->pluck('id')->all(),
        ];
    }
}
