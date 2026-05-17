<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Preferences\Services\ListMemoriesService;
use App\Models\User;
use Throwable;

/**
 * AUTO-READ. Returns the user's (or a team's) stored memories so the model
 * can recall facts that weren't in the always-on context. Useful before
 * calling `forget` (to resolve a memory by description to its key).
 */
final class ListMemoriesTool implements AiTool
{
    public function __construct(
        private ListMemoriesService $list,
    ) {}

    public function name(): string
    {
        return 'list_memories';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'List stored memories for the user (scope="user") or for a specific team (scope="team" '
            .'+ team_id). Returns the preference key and fact text for each. Note: memories are '
            .'already injected into the system prompt — call this only when the user explicitly asks '
            .'to review/manage them.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'scope' => ['type' => 'string', 'enum' => ['user', 'team']],
                'team_id' => ['type' => 'integer'],
            ],
            'required' => ['scope'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $scope = $arguments['scope'] ?? null;
        if (! is_string($scope) || ! in_array($scope, ['user', 'team'], true)) {
            return AiToolValidationResult::fail(['scope must be "user" or "team".']);
        }

        $normalized = ['scope' => $scope];
        if ($scope === 'team') {
            if (! isset($arguments['team_id']) || ! is_int($arguments['team_id'])) {
                return AiToolValidationResult::fail(['team_id is required for team-scoped memories.']);
            }
            $normalized['team_id'] = $arguments['team_id'];
        }

        return AiToolValidationResult::ok($normalized);
    }

    public function execute(User $user, array $arguments): array
    {
        try {
            $rows = $this->list->execute($user, (string) $arguments['scope'], $arguments['team_id'] ?? null);
        } catch (Throwable $e) {
            return ['error' => $e->getMessage()];
        }

        return [
            'count' => $rows->count(),
            'memories' => $rows->map(fn ($p) => [
                'key' => (string) $p->key,
                'fact' => (string) (is_array($p->value) ? ($p->value['fact'] ?? '') : ''),
                'created_at' => is_array($p->value) ? ($p->value['created_at'] ?? null) : null,
            ])->values()->all(),
        ];
    }
}
