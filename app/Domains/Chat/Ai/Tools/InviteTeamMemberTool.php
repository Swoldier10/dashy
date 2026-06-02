<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Services\FindTeamForUserService;
use App\Domains\Teams\Services\InviteTeamMemberService;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * CONFIRM-WRITE. Sends a team invitation email. validate() pre-checks what the
 * UI layer can know cheaply (team access, ownership, email shape, self-invite)
 * so the card surfaces those errors before Apply. The deeper business rules
 * (already a member, pending invitation, mail delivery) stay in
 * InviteTeamMemberService and deliberately bubble — ConfirmToolCallService
 * records them inline as a failed card.
 */
final class InviteTeamMemberTool implements AiTool
{
    public function __construct(
        private FindTeamForUserService $findTeamForUser,
        private InviteTeamMemberService $inviteTeamMember,
    ) {}

    public function name(): string
    {
        return 'invite_team_member';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Invite someone to a team the USER OWNS by sending them an email invitation. Provide '
            .'team_id, the recipient email EXACTLY as the user wrote it (never invent or guess an '
            .'email), and optionally role ("member" default, or "owner"). Only works for teams the '
            .'user owns and that are not personal teams. An actual invitation email is sent when the '
            .'user clicks Apply. See the system rules for the exact contract.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'email' => ['type' => 'string'],
                'role' => ['type' => 'string', 'enum' => ['owner', 'member']],
            ],
            'required' => ['team_id', 'email'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $teamId = $arguments['team_id'] ?? null;
        if (! is_int($teamId) && ! (is_string($teamId) && ctype_digit($teamId))) {
            return AiToolValidationResult::fail(['team_id is required and must be an integer.']);
        }
        $teamId = (int) $teamId;

        $team = $this->findTeamForUser->execute($user, $teamId);
        if ($team === null) {
            return AiToolValidationResult::fail(['You do not have access to that team.']);
        }
        if ($team->roleFor($user) !== TeamRole::Owner) {
            return AiToolValidationResult::fail(['Only the team owner can invite members.']);
        }
        if ($team->personal_team) {
            return AiToolValidationResult::fail(["You can't invite members to your personal team."]);
        }

        $email = $arguments['email'] ?? null;
        if (! is_string($email) || trim($email) === '') {
            return AiToolValidationResult::fail(['email is required.']);
        }
        $email = trim($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return AiToolValidationResult::fail(['email must be a valid email address.']);
        }
        if (Str::lower($email) === Str::lower((string) $user->email)) {
            return AiToolValidationResult::fail(["You can't invite yourself."]);
        }

        $role = $arguments['role'] ?? null;
        if ($role === null || $role === '') {
            $role = TeamRole::Member->value;
        }
        if (! is_string($role) || TeamRole::tryFrom($role) === null) {
            return AiToolValidationResult::fail(['role must be either "member" or "owner".']);
        }

        return AiToolValidationResult::ok([
            'team_id' => $teamId,
            'email' => $email,
            'role' => $role,
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $team = $this->findTeamForUser->execute($user, (int) $arguments['team_id']);
        if ($team === null) {
            return ['error' => 'You do not have access to that team.'];
        }

        $invitation = $this->inviteTeamMember->execute($user, $team, [
            'email' => (string) $arguments['email'],
            'role' => (string) ($arguments['role'] ?? TeamRole::Member->value),
        ]);

        return [
            'invitation_id' => $invitation->id,
            'team_id' => $team->id,
            'email' => (string) $invitation->email,
            'role' => $invitation->role->value,
        ];
    }
}
