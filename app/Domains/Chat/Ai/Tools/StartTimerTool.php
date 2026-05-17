<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\TimeTracking\Services\StartTimerService;
use App\Models\User;
use Throwable;

/**
 * CONFIRM-WRITE. Starts a running timer on a task. Any existing running timer
 * for the user is stopped first (the service handles that internally).
 */
final class StartTimerTool implements AiTool
{
    public function __construct(
        private StartTimerService $startTimer,
        private FindTaskAction $findTask,
    ) {}

    public function name(): string
    {
        return 'start_timer';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Start tracking time on a task. If the user already has a running timer it will be '
            .'stopped automatically before this one begins.';
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
        try {
            $task = $this->findTask->execute((int) $arguments['task_id']);
        } catch (Throwable) {
            return ['error' => 'Task not found.'];
        }

        $entry = $this->startTimer->execute($user, $task);

        return [
            'time_entry_id' => $entry->id,
            'task_id' => $task->id,
            'started_at' => $entry->started_at?->toIso8601String(),
        ];
    }
}
