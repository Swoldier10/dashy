<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Ai\Services\RecentActivityService;
use App\Domains\Chat\Models\Chat;
use App\Models\User;

/**
 * AUTO-READ. A cross-domain "what's going on lately" snapshot: open tasks
 * assigned to the user, recent time entries, and recent chats. Useful for
 * any "what's been happening?" style query.
 */
final class RecentActivityTool implements AiTool
{
    public function __construct(
        private RecentActivityService $recentActivity,
    ) {}

    public function name(): string
    {
        return 'recent_activity';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'Return a snapshot of the user\'s recent activity: open tasks, recent time entries, and '
            .'recent chats. Use this for "what\'s been happening" or "what was I working on" questions.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'per_bucket' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 30],
            ],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $normalized = [];
        if (isset($arguments['per_bucket']) && is_int($arguments['per_bucket'])) {
            $normalized['per_bucket'] = max(1, min(30, $arguments['per_bucket']));
        }

        return AiToolValidationResult::ok($normalized);
    }

    public function execute(User $user, array $arguments): array
    {
        return $this->recentActivity->execute($user, (int) ($arguments['per_bucket'] ?? 10));
    }
}
