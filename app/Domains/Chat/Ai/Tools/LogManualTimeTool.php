<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\TimeTracking\Services\LogManualTimeService;
use App\Models\User;
use Throwable;

/**
 * CONFIRM-WRITE. Logs a manual time entry for a task. Provide either a
 * duration string ("3h 20m", "45m") or an explicit started_at/ended_at pair.
 * If both are present, the explicit pair wins and duration is recomputed.
 */
final class LogManualTimeTool implements AiTool
{
    public function __construct(
        private LogManualTimeService $logTime,
        private FindTaskAction $findTask,
    ) {}

    public function name(): string
    {
        return 'log_manual_time';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Log a manual time entry on a task. Provide `duration` (e.g. "3h 20m") OR a started_at/'
            .'ended_at pair (ISO 8601). `notes` is optional.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_id' => ['type' => 'integer'],
                'duration' => ['type' => 'string', 'maxLength' => 32],
                'started_at' => ['type' => 'string'],
                'ended_at' => ['type' => 'string'],
                'notes' => ['type' => 'string', 'maxLength' => 2000],
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

        $hasDuration = ! empty($arguments['duration']);
        $hasStart = ! empty($arguments['started_at']);
        $hasEnd = ! empty($arguments['ended_at']);

        if (! $hasDuration && ! ($hasStart && $hasEnd)) {
            return AiToolValidationResult::fail(['Provide either duration, or both started_at and ended_at.']);
        }

        $normalized = ['task_id' => $taskId];
        foreach (['duration', 'started_at', 'ended_at', 'notes'] as $key) {
            if (isset($arguments[$key]) && is_string($arguments[$key])) {
                $normalized[$key] = $arguments[$key];
            }
        }

        return AiToolValidationResult::ok($normalized);
    }

    public function execute(User $user, array $arguments): array
    {
        try {
            $task = $this->findTask->execute((int) $arguments['task_id']);
        } catch (Throwable) {
            return ['error' => 'Task not found.'];
        }

        $entry = $this->logTime->execute($user, $task, $arguments);

        return [
            'time_entry_id' => $entry->id,
            'task_id' => $task->id,
            'duration_seconds' => (int) ($entry->duration_seconds ?? 0),
        ];
    }
}
