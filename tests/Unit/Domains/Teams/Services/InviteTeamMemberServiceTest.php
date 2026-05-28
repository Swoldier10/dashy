<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Mail\TeamInvitationMail;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Domains\Teams\Services\InviteTeamMemberService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InviteTeamMemberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_invite_member_and_mail_is_sent(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner(ownerName: 'Anna Owner', teamName: 'Acme Studio');

        $invitation = app(InviteTeamMemberService::class)->execute($owner, $team, [
            'email' => 'Newbie@example.com',
            'role' => 'member',
        ]);

        $this->assertSame('newbie@example.com', $invitation->email);
        $this->assertSame(TeamRole::Member, $invitation->role);
        $this->assertSame($owner->id, $invitation->invited_by_user_id);
        $this->assertNull($invitation->accepted_at);
        $this->assertNotNull($invitation->token_hash);
        $this->assertTrue($invitation->expires_at->isFuture());

        Mail::assertSent(TeamInvitationMail::class, function ($mail) use ($team, $invitation) {
            $html = $mail->render();

            return $mail->hasTo('newbie@example.com')
                && $mail->invitation->id === $invitation->id
                && $mail->invitation->team->is($team)
                && str_contains($html, 'Acme Studio')
                && str_contains($html, 'Anna Owner')
                && str_contains($html, route('invite.show', ['token' => $mail->plainToken]))
                && str_contains($html, __('Member'));
        });
    }

    public function test_non_owner_is_forbidden(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        $this->expectException(AuthorizationException::class);

        app(InviteTeamMemberService::class)->execute($member, $team, [
            'email' => 'somebody@example.com',
            'role' => 'member',
        ]);
    }

    public function test_self_invite_rejected(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();

        $this->expectException(ValidationException::class);

        app(InviteTeamMemberService::class)->execute($owner, $team, [
            'email' => $owner->email,
            'role' => 'member',
        ]);
    }

    public function test_existing_member_rejected(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();
        $member = User::factory()->create(['email' => 'already@example.com']);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        $this->expectException(ValidationException::class);

        app(InviteTeamMemberService::class)->execute($owner, $team, [
            'email' => 'already@example.com',
            'role' => 'member',
        ]);
    }

    public function test_duplicate_pending_invitation_rejected(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'pending@example.com',
        ]);

        $this->expectException(ValidationException::class);

        app(InviteTeamMemberService::class)->execute($owner, $team, [
            'email' => 'pending@example.com',
            'role' => 'member',
        ]);
    }

    public function test_invalid_email_format_rejected(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();

        $this->expectException(ValidationException::class);

        app(InviteTeamMemberService::class)->execute($owner, $team, [
            'email' => 'not-an-email',
            'role' => 'member',
        ]);
    }

    public function test_invalid_role_rejected(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();

        $this->expectException(ValidationException::class);

        app(InviteTeamMemberService::class)->execute($owner, $team, [
            'email' => 'fine@example.com',
            'role' => 'admin', // not a valid TeamRole
        ]);
    }

    public function test_owner_role_can_be_invited(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();

        $invitation = app(InviteTeamMemberService::class)->execute($owner, $team, [
            'email' => 'co-owner@example.com',
            'role' => 'owner',
        ]);

        $this->assertSame(TeamRole::Owner, $invitation->role);
    }

    /** @return array{0: User, 1: Team} */
    private function makeTeamWithOwner(?string $ownerName = null, ?string $teamName = null): array
    {
        $owner = User::factory()->create($ownerName !== null ? ['name' => $ownerName] : []);
        $team = Team::factory()->create($teamName !== null ? ['name' => $teamName] : []);
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        return [$owner, $team];
    }
}
