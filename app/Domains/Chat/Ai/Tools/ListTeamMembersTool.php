<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Services\ListTeamMembersService;
use App\Models\User;
use Throwable;

/**
 * AUTO-READ. Lists members of a team the user belongs to, including their
 * role (owner/member). Use to resolve names when the user says e.g.
 * "assign Anna" and we need her user_id.
 */
final class ListTeamMembersTool implements AiTool
{
    public function __construct(
        private ListTeamMembersService $listMembers,
    ) {}

    public function name(): string
    {
        return 'list_team_members';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'List every member of a team the user belongs to, with id, name, email, and role. '
            .'Use this to resolve a name to a user_id before calling assignment tools.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
            ],
            'required' => ['team_id'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $id = $arguments['team_id'] ?? null;
        if (! is_int($id)) {
            return AiToolValidationResult::fail(['team_id is required and must be an integer.']);
        }

        return AiToolValidationResult::ok(['team_id' => $id]);
    }

    public function execute(User $user, array $arguments): array
    {
        try {
            $members = $this->listMembers->execute($user, (int) $arguments['team_id']);
        } catch (Throwable) {
            return ['error' => 'Team not found or not accessible.'];
        }

        return [
            'team_id' => (int) $arguments['team_id'],
            'count' => $members->count(),
            'members' => $members->map(function (User $m) {
                $role = $m->pivot->role ?? null;
                $roleValue = $role instanceof TeamRole ? $role->value : (string) $role;

                return [
                    'id' => $m->id,
                    'name' => (string) $m->name,
                    'email' => (string) $m->email,
                    'role' => $roleValue,
                ];
            })->values()->all(),
        ];
    }
}
