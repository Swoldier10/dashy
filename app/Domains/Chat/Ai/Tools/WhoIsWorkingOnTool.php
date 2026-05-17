<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\WhoIsWorkingOnService;
use App\Models\User;

/**
 * AUTO-READ. Who is currently running a timer across teams the user belongs
 * to. Each result includes the worker (id/name), the task they're on, and
 * how long the timer has been running.
 */
final class WhoIsWorkingOnTool implements AiTool
{
    public function __construct(
        private WhoIsWorkingOnService $whoIsOn,
    ) {}

    public function name(): string
    {
        return 'who_is_working_on';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'See who is currently tracking time on which task, scoped to teams the user belongs to. '
            .'Empty list means no one is running a timer right now.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => new \stdClass,
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        return AiToolValidationResult::ok([]);
    }

    public function execute(User $user, array $arguments): array
    {
        $timers = $this->whoIsOn->execute($user);
        $now = now();

        return [
            'count' => $timers->count(),
            'timers' => $timers->map(fn (TimeEntry $entry) => [
                'time_entry_id' => $entry->id,
                'user' => $entry->user !== null ? [
                    'id' => $entry->user->id,
                    'name' => (string) $entry->user->name,
                ] : null,
                'task' => $entry->task !== null ? [
                    'id' => $entry->task->id,
                    'name' => (string) $entry->task->name,
                    'project_id' => $entry->task->project_id,
                ] : null,
                'started_at' => $entry->started_at?->toIso8601String(),
                'running_seconds' => $entry->started_at !== null
                    ? max(0, $now->diffInSeconds($entry->started_at, absolute: true))
                    : 0,
            ])->values()->all(),
        ];
    }
}
