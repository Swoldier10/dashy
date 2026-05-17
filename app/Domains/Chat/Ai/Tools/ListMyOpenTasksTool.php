<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ListUserOpenTasksService;
use App\Models\User;

/**
 * AUTO-READ. Lists tasks assigned to the user that are not yet in a "done"
 * status, across every team they belong to. Capped at 50 results.
 */
final class ListMyOpenTasksTool implements AiTool
{
    public function __construct(
        private ListUserOpenTasksService $listOpen,
    ) {}

    public function name(): string
    {
        return 'list_my_open_tasks';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'List the current user\'s open (non-done) tasks across every team they belong to. '
            .'Use this when the user asks about their workload, what they\'re working on, what\'s on '
            .'their plate, etc.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
            ],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $normalized = [];
        if (isset($arguments['limit']) && is_int($arguments['limit'])) {
            $normalized['limit'] = max(1, min(100, $arguments['limit']));
        }

        return AiToolValidationResult::ok($normalized);
    }

    public function execute(User $user, array $arguments): array
    {
        $tasks = $this->listOpen->execute($user, onlyMine: true, limit: (int) ($arguments['limit'] ?? 50));

        return [
            'count' => $tasks->count(),
            'tasks' => $tasks->map(fn (Task $t) => [
                'id' => $t->id,
                'name' => (string) $t->name,
                'project_id' => $t->project_id,
                'project_name' => $t->project?->name,
                'status_name' => $t->status?->name,
                'status_category' => $t->status?->category->value,
                'priority' => $t->priority?->value,
                'end_date' => $t->end_date?->toDateString(),
            ])->values()->all(),
        ];
    }
}
