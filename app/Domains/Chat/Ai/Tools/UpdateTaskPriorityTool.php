<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Services\UpdateTaskPriorityService;
use App\Models\User;

/**
 * CONFIRM-WRITE. Changes a task's priority. Priority values: urgent | high
 * | normal | low (TaskPriority enum).
 */
final class UpdateTaskPriorityTool implements AiTool
{
    public function __construct(
        private UpdateTaskPriorityService $updatePriority,
    ) {}

    public function name(): string
    {
        return 'update_task_priority';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Set a task\'s priority. Allowed values: urgent, high, normal, low.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_id' => ['type' => 'integer'],
                'priority' => [
                    'type' => 'string',
                    'enum' => array_map(fn (TaskPriority $p) => $p->value, TaskPriority::cases()),
                ],
            ],
            'required' => ['task_id', 'priority'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $taskId = $arguments['task_id'] ?? null;
        $priority = $arguments['priority'] ?? null;

        if (! is_int($taskId)) {
            return AiToolValidationResult::fail(['task_id is required and must be an integer.']);
        }
        if (! is_string($priority) || TaskPriority::tryFrom($priority) === null) {
            return AiToolValidationResult::fail(['priority must be one of: urgent, high, normal, low.']);
        }

        return AiToolValidationResult::ok([
            'task_id' => $taskId,
            'priority' => $priority,
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $task = $this->updatePriority->execute(
            $user,
            (int) $arguments['task_id'],
            (string) $arguments['priority'],
        );

        return ['task_id' => $task->id, 'priority' => $task->priority?->value];
    }
}
