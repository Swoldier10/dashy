<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\TimeTracking\Services\ProjectTimeStatsService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Throwable;

/**
 * AUTO-READ. Time-tracking summary for a project: total seconds (all-time
 * or filtered by user) plus an optional daily breakdown for a specific month.
 */
final class GetTimeSummaryTool implements AiTool
{
    public function __construct(
        private ProjectTimeStatsService $timeStats,
    ) {}

    public function name(): string
    {
        return 'get_time_summary';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'Return total tracked seconds for a project, optionally filtered by user_id, and an optional '
            .'daily breakdown for a single month. `month` must be YYYY-MM if provided.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'project_id' => ['type' => 'integer'],
                'user_id' => ['type' => 'integer'],
                'month' => ['type' => 'string', 'pattern' => '^\\d{4}-\\d{2}$'],
            ],
            'required' => ['project_id'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $projectId = $arguments['project_id'] ?? null;
        if (! is_int($projectId)) {
            return AiToolValidationResult::fail(['project_id is required and must be an integer.']);
        }

        $normalized = ['project_id' => $projectId];

        if (isset($arguments['user_id']) && is_int($arguments['user_id'])) {
            $normalized['user_id'] = $arguments['user_id'];
        }

        if (isset($arguments['month'])) {
            $month = (string) $arguments['month'];
            if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
                return AiToolValidationResult::fail(['month must be in YYYY-MM format.']);
            }
            $normalized['month'] = $month;
        }

        return AiToolValidationResult::ok($normalized);
    }

    public function execute(User $user, array $arguments): array
    {
        $projectId = (int) $arguments['project_id'];
        $userId = isset($arguments['user_id']) ? (int) $arguments['user_id'] : null;

        try {
            $total = $this->timeStats->totalSecondsForProject($user, $projectId, $userId);
        } catch (Throwable $e) {
            return ['error' => 'Project not accessible.'];
        }

        $result = [
            'project_id' => $projectId,
            'user_id' => $userId,
            'total_seconds' => $total,
        ];

        if (isset($arguments['month'])) {
            try {
                $anchor = CarbonImmutable::createFromFormat('Y-m', (string) $arguments['month'])?->startOfMonth();
                if ($anchor !== null) {
                    $result['daily_seconds'] = $this->timeStats->dailyHoursForMonth($user, $projectId, $anchor, $userId);
                    $result['month'] = (string) $arguments['month'];
                }
            } catch (Throwable) {
                // Daily breakdown is optional; surface only the total on failure.
            }
        }

        return $result;
    }
}
