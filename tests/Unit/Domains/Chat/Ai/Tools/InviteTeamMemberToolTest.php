<?php

namespace Tests\Unit\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Tools\InviteTeamMemberTool;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Mail\TeamInvitationMail;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteTeamMemberToolTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: Team} */
    private function ownerWithTeam(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        return [$owner, $team];
    }

    public function test_validate_rejects_missing_team_id(): void
    {
        $user = User::factory()->create();

        $result = app(InviteTeamMemberTool::class)->validate($user, ['email' => 'a@b.cd']);

        $this->assertFalse($result->valid);
        $this->assertNotEmpty($result->errors);
    }

    public function test_validate_rejects_team_user_is_not_member_of(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(); // user NOT attached

        $result = app(InviteTeamMemberTool::class)->validate($user, [
            'team_id' => $team->id,
            'email' => 'a@b.cd',
        ]);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString('do not have access', implode(' ', $result->errors));
    }

    public function test_validate_rejects_non_owner(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $result = app(InviteTeamMemberTool::class)->validate($user, [
            'team_id' => $team->id,
            'email' => 'a@b.cd',
        ]);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString('Only the team owner', implode(' ', $result->errors));
    }

    public function test_validate_rejects_personal_team(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->personal()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        $result = app(InviteTeamMemberTool::class)->validate($owner, [
            'team_id' => $team->id,
            'email' => 'a@b.cd',
        ]);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString('personal team', implode(' ', $result->errors));
    }

    public function test_validate_rejects_missing_email(): void
    {
        [$owner, $team] = $this->ownerWithTeam();

        $result = app(InviteTeamMemberTool::class)->validate($owner, ['team_id' => $team->id]);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString('email is required', implode(' ', $result->errors));
    }

    public function test_validate_rejects_invalid_email(): void
    {
        [$owner, $team] = $this->ownerWithTeam();

        $result = app(InviteTeamMemberTool::class)->validate($owner, [
            'team_id' => $team->id,
            'email' => 'not-an-email',
        ]);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString('valid email', implode(' ', $result->errors));
    }

    public function test_validate_rejects_self_invite_case_insensitively(): void
    {
        [$owner, $team] = $this->ownerWithTeam();

        $result = app(InviteTeamMemberTool::class)->validate($owner, [
            'team_id' => $team->id,
            'email' => mb_strtoupper((string) $owner->email),
        ]);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString("can't invite yourself", implode(' ', $result->errors));
    }

    public function test_validate_rejects_invalid_role(): void
    {
        [$owner, $team] = $this->ownerWithTeam();

        $result = app(InviteTeamMemberTool::class)->validate($owner, [
            'team_id' => $team->id,
            'email' => 'a@b.cd',
            'role' => 'admin',
        ]);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString('role must be', implode(' ', $result->errors));
    }

    public function test_validate_defaults_role_to_member_when_absent(): void
    {
        [$owner, $team] = $this->ownerWithTeam();

        $result = app(InviteTeamMemberTool::class)->validate($owner, [
            'team_id' => $team->id,
            'email' => 'a@b.cd',
        ]);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame(TeamRole::Member->value, $result->normalized['role']);
    }

    public function test_validate_happy_path_accepts_both_roles_and_trims_email(): void
    {
        [$owner, $team] = $this->ownerWithTeam();
        $tool = app(InviteTeamMemberTool::class);

        $asMember = $tool->validate($owner, [
            'team_id' => $team->id,
            'email' => '  newbie@example.com  ',
            'role' => TeamRole::Member->value,
        ]);
        $this->assertTrue($asMember->valid, implode(', ', $asMember->errors));
        $this->assertSame($team->id, $asMember->normalized['team_id']);
        $this->assertSame('newbie@example.com', $asMember->normalized['email']);
        $this->assertSame(TeamRole::Member->value, $asMember->normalized['role']);

        $asOwner = $tool->validate($owner, [
            'team_id' => $team->id,
            'email' => 'boss@example.com',
            'role' => TeamRole::Owner->value,
        ]);
        $this->assertTrue($asOwner->valid, implode(', ', $asOwner->errors));
        $this->assertSame(TeamRole::Owner->value, $asOwner->normalized['role']);
    }

    public function test_execute_sends_invitation_and_returns_payload(): void
    {
        Mail::fake();
        [$owner, $team] = $this->ownerWithTeam();

        $result = app(InviteTeamMemberTool::class)->execute($owner, [
            'team_id' => $team->id,
            'email' => 'newbie@example.com',
            'role' => TeamRole::Member->value,
        ]);

        $this->assertArrayNotHasKey('error', $result);
        $this->assertSame($team->id, $result['team_id']);
        $this->assertSame('newbie@example.com', $result['email']);
        $this->assertSame(TeamRole::Member->value, $result['role']);

        $invitation = TeamInvitation::query()->findOrFail($result['invitation_id']);
        $this->assertSame('newbie@example.com', $invitation->email);
        $this->assertSame($team->id, $invitation->team_id);

        Mail::assertSent(TeamInvitationMail::class, 1);
    }

    public function test_execute_returns_error_when_team_access_lost(): void
    {
        Mail::fake();
        [$owner, $team] = $this->ownerWithTeam();
        // Simulate concurrent removal between preview and confirm.
        $team->members()->detach($owner->id);

        $result = app(InviteTeamMemberTool::class)->execute($owner, [
            'team_id' => $team->id,
            'email' => 'newbie@example.com',
            'role' => TeamRole::Member->value,
        ]);

        $this->assertSame(['error' => 'You do not have access to that team.'], $result);
        $this->assertSame(0, TeamInvitation::query()->count());
        Mail::assertNothingSent();
    }
}
