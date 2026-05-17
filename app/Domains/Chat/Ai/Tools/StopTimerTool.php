<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\TimeTracking\Services\StopTimerService;
use App\Models\User;
use Throwable;

/**
 * CONFIRM-WRITE. Stops the user's currently running timer (if any). No
 * arguments; the service throws cleanly when nothing is running.
 */
final class StopTimerTool implements AiTool
{
    public function __construct(
        private StopTimerService $stopTimer,
    ) {}

    public function name(): string
    {
        return 'stop_timer';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Stop the user\'s currently running timer. No arguments needed. Fails if no timer is '
            .'currently running.';
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
        try {
            $entry = $this->stopTimer->execute($user);
        } catch (Throwable $e) {
            return ['error' => $e->getMessage()];
        }

        return [
            'time_entry_id' => $entry->id,
            'task_id' => $entry->task_id,
            'duration_seconds' => (int) ($entry->duration_seconds ?? 0),
            'ended_at' => $entry->ended_at?->toIso8601String(),
        ];
    }
}
