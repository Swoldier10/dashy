<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\UnarchiveTaskService;
use App\Models\User;

/**
 * CONFIRM-WRITE. Restores an archived task to the active board.
 */
final class UnarchiveTaskTool implements AiTool
{
    public function __construct(
        private UnarchiveTaskService $unarchive,
    ) {}

    public function name(): string
    {
        return 'unarchive_task';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Restore a previously archived task to the active board.';
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
        $task = $this->unarchive->execute($user, (int) $arguments['task_id']);

        return ['task_id' => $task->id, 'is_archived' => false];
    }
}
