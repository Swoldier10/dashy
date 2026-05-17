<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\UpdateTaskStatusService;
use App\Models\User;

/**
 * CONFIRM-WRITE. Moves a task into a different status within the same
 * project. The card shows "Move 'X' → 'Done'".
 */
final class MoveTaskToStatusTool implements AiTool
{
    public function __construct(
        private UpdateTaskStatusService $moveStatus,
    ) {}

    public function name(): string
    {
        return 'move_task_to_status';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Move a task to a different status within the same project. Status must belong to the '
            .'task\'s project — use list_tasks or get_project_overview to find valid status ids.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_id' => ['type' => 'integer'],
                'target_status_id' => ['type' => 'integer'],
            ],
            'required' => ['task_id', 'target_status_id'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $taskId = $arguments['task_id'] ?? null;
        $targetStatusId = $arguments['target_status_id'] ?? null;

        if (! is_int($taskId)) {
            return AiToolValidationResult::fail(['task_id is required and must be an integer.']);
        }
        if (! is_int($targetStatusId)) {
            return AiToolValidationResult::fail(['target_status_id is required and must be an integer.']);
        }

        return AiToolValidationResult::ok([
            'task_id' => $taskId,
            'target_status_id' => $targetStatusId,
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $task = $this->moveStatus->execute(
            $user,
            (int) $arguments['task_id'],
            (int) $arguments['target_status_id'],
        );

        return [
            'task_id' => $task->id,
            'project_status_id' => $task->project_status_id,
        ];
    }
}
