<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Preferences\Services\ForgetMemoryService;
use App\Models\User;

/**
 * CONFIRM-WRITE. Deletes a stored memory by its preference key. The key is
 * surfaced by list_memories — the assistant should call list_memories first
 * if the user refers to a memory by content rather than key.
 */
final class ForgetTool implements AiTool
{
    public function __construct(
        private ForgetMemoryService $forget,
    ) {}

    public function name(): string
    {
        return 'forget';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Delete a stored memory by its preference key (e.g. "memory.ab12cd34"). Use '
            .'list_memories first if you only know the memory by description rather than key.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'scope' => ['type' => 'string', 'enum' => ['user', 'team']],
                'key' => ['type' => 'string', 'maxLength' => 64],
                'team_id' => ['type' => 'integer'],
            ],
            'required' => ['scope', 'key'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $scope = $arguments['scope'] ?? null;
        $key = $arguments['key'] ?? null;

        if (! is_string($scope) || ! in_array($scope, ['user', 'team'], true)) {
            return AiToolValidationResult::fail(['scope must be "user" or "team".']);
        }
        if (! is_string($key) || trim($key) === '') {
            return AiToolValidationResult::fail(['key is required.']);
        }

        $normalized = ['scope' => $scope, 'key' => trim($key)];
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
        $deleted = $this->forget->execute(
            $user,
            (string) $arguments['scope'],
            (string) $arguments['key'],
            $arguments['team_id'] ?? null,
        );

        return [
            'key' => (string) $arguments['key'],
            'scope' => (string) $arguments['scope'],
            'deleted' => $deleted,
        ];
    }
}
