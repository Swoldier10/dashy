<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\UpdateTaskService;
use App\Models\User;

/**
 * CONFIRM-WRITE. Replaces a task's full description. Use create_task's
 * "Beschreibung / Akzeptanzkriterien" Markdown structure for the body.
 */
final class UpdateTaskDescriptionTool implements AiTool
{
    public function __construct(
        private UpdateTaskService $updateTask,
    ) {}

    public function name(): string
    {
        return 'update_task_description';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Replace a task\'s description (Markdown, up to 5000 chars). Match the project\'s task '
            .'writing language. The card shows the proposed new description; the user can discard if '
            .'wrong.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_id' => ['type' => 'integer'],
                'description' => ['type' => 'string', 'maxLength' => 5000],
            ],
            'required' => ['task_id', 'description'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $taskId = $arguments['task_id'] ?? null;
        $description = $arguments['description'] ?? null;

        if (! is_int($taskId)) {
            return AiToolValidationResult::fail(['task_id is required and must be an integer.']);
        }
        if (! is_string($description)) {
            return AiToolValidationResult::fail(['description must be a string.']);
        }
        if (mb_strlen($description) > 5000) {
            return AiToolValidationResult::fail(['description must be 5000 characters or fewer.']);
        }

        return AiToolValidationResult::ok([
            'task_id' => $taskId,
            'description' => $description,
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $task = $this->updateTask->execute($user, (int) $arguments['task_id'], [
            'description' => (string) $arguments['description'],
        ]);

        return ['task_id' => $task->id];
    }
}
