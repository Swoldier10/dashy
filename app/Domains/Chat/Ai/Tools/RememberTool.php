<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Preferences\Services\RecordMemoryService;
use App\Models\User;

/**
 * CONFIRM-WRITE. Persists a free-form memory fact at user or team scope. The
 * user reviews the proposed memory on the card before it lands. Future chat
 * sessions inject these into the system prompt so the assistant adapts.
 */
final class RememberTool implements AiTool
{
    public function __construct(
        private RecordMemoryService $record,
    ) {}

    public function name(): string
    {
        return 'remember';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Persist a fact about the user or team so future chat sessions can use it. Use '
            .'scope="user" for personal preferences ("prefers kanban view", "default project is X"). '
            .'Use scope="team" with a team_id for team-wide conventions ("uses Fibonacci points"). '
            .'The fact will appear under USER MEMORIES / TEAM CONVENTIONS in the next system prompt.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'scope' => ['type' => 'string', 'enum' => ['user', 'team']],
                'fact' => ['type' => 'string', 'minLength' => 2, 'maxLength' => 2000],
                'team_id' => ['type' => 'integer'],
            ],
            'required' => ['scope', 'fact'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $scope = $arguments['scope'] ?? null;
        $fact = $arguments['fact'] ?? null;

        if (! is_string($scope) || ! in_array($scope, ['user', 'team'], true)) {
            return AiToolValidationResult::fail(['scope must be "user" or "team".']);
        }
        if (! is_string($fact) || trim($fact) === '') {
            return AiToolValidationResult::fail(['fact is required.']);
        }

        $normalized = ['scope' => $scope, 'fact' => trim($fact)];

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
        $pref = $this->record->execute(
            $user,
            (string) $arguments['scope'],
            (string) $arguments['fact'],
            $arguments['team_id'] ?? null,
        );

        return [
            'preference_key' => $pref->key,
            'scope' => (string) $arguments['scope'],
        ];
    }
}
