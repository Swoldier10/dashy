<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\ArchiveTaskService;
use App\Models\User;

/**
 * CONFIRM-WRITE. Archives a task (sets is_archived=true). The task stays in
 * the DB and can be unarchived later.
 */
final class ArchiveTaskTool implements AiTool
{
    public function __construct(
        private ArchiveTaskService $archive,
    ) {}

    public function name(): string
    {
        return 'archive_task';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Archive a task so it stops appearing in the active board. Reversible via unarchive_task.';
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
        $taskId = $arguments['task_id'] ?? null;
        if (! is_int($taskId)) {
            return AiToolValidationResult::fail(['task_id is required and must be an integer.']);
        }

        return AiToolValidationResult::ok(['task_id' => $taskId]);
    }

    public function execute(User $user, array $arguments): array
    {
        $task = $this->archive->execute($user, (int) $arguments['task_id']);

        return ['task_id' => $task->id, 'is_archived' => true];
    }
}
