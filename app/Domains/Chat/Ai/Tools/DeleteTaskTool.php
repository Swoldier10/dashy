<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\DeleteTaskService;
use App\Models\User;

/**
 * CONFIRM-WRITE (destructive). Hard-deletes a single task. Prefer
 * archive_task unless the user explicitly says delete; the card calls this
 * out with a red Apply button and a warning.
 */
final class DeleteTaskTool implements AiTool
{
    public function __construct(
        private DeleteTaskService $deleteTask,
    ) {}

    public function name(): string
    {
        return 'delete_task';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Hard-delete a task. This cannot be undone. Prefer archive_task unless the user '
            .'explicitly says delete or remove.';
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
        $this->deleteTask->execute($user, (int) $arguments['task_id']);

        return ['task_id' => (int) $arguments['task_id'], 'deleted' => true];
    }
}
