<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\UpdateTaskService;
use App\Models\User;

/**
 * CONFIRM-WRITE. Renames a single task. The user sees a card "Rename task
 * #42 → '<new>'" and clicks Apply or Discard.
 */
final class UpdateTaskNameTool implements AiTool
{
    public function __construct(
        private UpdateTaskService $updateTask,
    ) {}

    public function name(): string
    {
        return 'update_task_name';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Rename a task. Provide the task id and the new name (max 200 chars). The card the user '
            .'confirms shows the old → new transition.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_id' => ['type' => 'integer'],
                'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 200],
            ],
            'required' => ['task_id', 'name'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $errors = [];
        $taskId = $arguments['task_id'] ?? null;
        $name = $arguments['name'] ?? null;

        if (! is_int($taskId)) {
            $errors[] = 'task_id is required and must be an integer.';
        }
        if (! is_string($name) || trim($name) === '') {
            $errors[] = 'name is required and must be a non-empty string.';
        } elseif (mb_strlen($name) > 200) {
            $errors[] = 'name must be 200 characters or fewer.';
        }

        if ($errors !== []) {
            return AiToolValidationResult::fail($errors);
        }

        return AiToolValidationResult::ok([
            'task_id' => $taskId,
            'name' => trim((string) $name),
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $task = $this->updateTask->execute($user, (int) $arguments['task_id'], [
            'name' => (string) $arguments['name'],
        ]);

        return ['task_id' => $task->id, 'name' => (string) $task->name];
    }
}
