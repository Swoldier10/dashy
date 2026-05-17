<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ListOverdueTasksService;
use App\Models\User;

/**
 * AUTO-READ. Lists overdue tasks across the user's teams — `end_date` strictly
 * before today AND not in a "done" status. Set `only_mine` to narrow to the
 * actor's own assignments.
 */
final class ListOverdueTasksTool implements AiTool
{
    public function __construct(
        private ListOverdueTasksService $listOverdue,
    ) {}

    public function name(): string
    {
        return 'list_overdue_tasks';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'List tasks whose end date is in the past and which are not yet done, across every team '
            .'the user belongs to. Pass `only_mine=true` to restrict to the current user\'s assignments.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'only_mine' => ['type' => 'boolean'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
            ],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $normalized = [];
        if (array_key_exists('only_mine', $arguments)) {
            $normalized['only_mine'] = (bool) $arguments['only_mine'];
        }
        if (isset($arguments['limit']) && is_int($arguments['limit'])) {
            $normalized['limit'] = max(1, min(100, $arguments['limit']));
        }

        return AiToolValidationResult::ok($normalized);
    }

    public function execute(User $user, array $arguments): array
    {
        $tasks = $this->listOverdue->execute(
            $user,
            onlyMine: (bool) ($arguments['only_mine'] ?? false),
            limit: (int) ($arguments['limit'] ?? 50),
        );

        return [
            'count' => $tasks->count(),
            'tasks' => $tasks->map(fn (Task $t) => [
                'id' => $t->id,
                'name' => (string) $t->name,
                'project_id' => $t->project_id,
                'project_name' => $t->project?->name,
                'end_date' => $t->end_date?->toDateString(),
                'days_overdue' => $t->end_date !== null
                    ? max(0, $t->end_date->diffInDays(now()->toDateString()))
                    : null,
                'assignee_ids' => $t->assignees->pluck('id')->all(),
            ])->values()->all(),
        ];
    }
}
