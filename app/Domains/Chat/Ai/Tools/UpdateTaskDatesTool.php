<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Services\UpdateTaskDatesService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Throwable;

/**
 * CONFIRM-WRITE. Sets a task's start/end dates. Either may be omitted to
 * clear it; if both are present, end must be on or after start.
 */
final class UpdateTaskDatesTool implements AiTool
{
    public function __construct(
        private UpdateTaskDatesService $updateDates,
    ) {}

    public function name(): string
    {
        return 'update_task_dates';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Set a task\'s start_date and/or end_date (YYYY-MM-DD). Pass null to clear a date. '
            .'`end_date` must be on or after `start_date`.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_id' => ['type' => 'integer'],
                'start_date' => ['type' => ['string', 'null'], 'format' => 'date'],
                'end_date' => ['type' => ['string', 'null'], 'format' => 'date'],
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

        $normalized = ['task_id' => $taskId];

        foreach (['start_date', 'end_date'] as $key) {
            if (! array_key_exists($key, $arguments)) {
                continue;
            }
            $value = $arguments[$key];
            if ($value === null) {
                $normalized[$key] = null;

                continue;
            }
            if (! is_string($value)) {
                return AiToolValidationResult::fail([$key.' must be a YYYY-MM-DD string or null.']);
            }
            try {
                CarbonImmutable::parse($value);
            } catch (Throwable) {
                return AiToolValidationResult::fail([$key.' must be a valid date.']);
            }
            $normalized[$key] = $value;
        }

        if (
            isset($normalized['start_date'], $normalized['end_date'])
            && $normalized['start_date'] !== null
            && $normalized['end_date'] !== null
            && CarbonImmutable::parse($normalized['end_date'])->lessThan(CarbonImmutable::parse($normalized['start_date']))
        ) {
            return AiToolValidationResult::fail(['end_date must be on or after start_date.']);
        }

        return AiToolValidationResult::ok($normalized);
    }

    public function execute(User $user, array $arguments): array
    {
        $task = $this->updateDates->execute(
            $user,
            (int) $arguments['task_id'],
            $arguments['start_date'] ?? null,
            $arguments['end_date'] ?? null,
        );

        return [
            'task_id' => $task->id,
            'start_date' => $task->start_date?->toDateString(),
            'end_date' => $task->end_date?->toDateString(),
        ];
    }
}
