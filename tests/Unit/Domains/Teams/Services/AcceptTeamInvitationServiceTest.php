<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Exceptions\InvalidInvitationException;
use App\Domains\Teams\Exceptions\InvitationAlreadyAcceptedException;
use App\Domains\Teams\Exceptions\InvitationEmailMismatchException;
use App\Domains\Teams\Exceptions\InvitationExpiredException;
use App\Domains\Teams\Exceptions\InvitationRevokedException;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Domains\Teams\Services\AcceptTeamInvitationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptTeamInvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_happy_path_attaches_user_and_marks_consumed(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['email' => 'accept@example.com']);
        $token = 'plain-token-xyz';
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'accept@example.com',
            'token_hash' => hash('sha256', $token),
            'role' => TeamRole::Member,
        ]);

        $result = app(AcceptTeamInvitationService::class)->execute($user, $token);

        $this->assertTrue($result->is($invitation));
        $this->assertNotNull($result->accepted_at);
        $this->assertSame($user->id, $result->accepted_by_user_id);
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => TeamRole::Member->value,
        ]);
    }

    public function test_invalid_token_throws(): void
    {
        $user = User::factory()->create();

        $this->expectException(InvalidInvitationException::class);

        app(AcceptTeamInvitationService::class)->execute($user, 'no-such-token');
    }

    public function test_revoked_invitation_throws(): void
    {
        $user = User::factory()->create(['email' => 'r@example.com']);
        $token = 'tok-r';
        TeamInvitation::factory()->revoked()->create([
            'email' => 'r@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $this->expectException(InvitationRevokedException::class);

        app(AcceptTeamInvitationService::class)->execute($user, $token);
    }

    public function test_expired_invitation_throws(): void
    {
        $user = User::factory()->create(['email' => 'x@example.com']);
        $token = 'tok-x';
        TeamInvitation::factory()->expired()->create([
            'email' => 'x@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $this->expectException(InvitationExpiredException::class);

        app(AcceptTeamInvitationService::class)->execute($user, $token);
    }

    public function test_email_mismatch_throws(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);
        $token = 'tok-m';
        TeamInvitation::factory()->create([
            'email' => 'them@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $this->expectException(InvitationEmailMismatchException::class);

        app(AcceptTeamInvitationService::class)->execute($user, $token);
    }

    public function test_already_accepted_by_same_user_is_idempotent(): void
    {
        $user = User::factory()->create(['email' => 'i@example.com']);
        $token = 'tok-i';
        $invitation = TeamInvitation::factory()->create([
            'email' => 'i@example.com',
            'token_hash' => hash('sha256', $token),
            'accepted_at' => now(),
            'accepted_by_user_id' => $user->id,
        ]);

        $result = app(AcceptTeamInvitationService::class)->execute($user, $token);

        $this->assertTrue($result->is($invitation));
    }

    public function test_already_accepted_by_other_user_throws(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);
        $other = User::factory()->create();
        $token = 'tok-o';
        TeamInvitation::factory()->create([
            'email' => 'me@example.com',
            'token_hash' => hash('sha256', $token),
            'accepted_at' => now(),
            'accepted_by_user_id' => $other->id,
        ]);

        $this->expectException(InvitationAlreadyAcceptedException::class);

        app(AcceptTeamInvitationService::class)->execute($user, $token);
    }

    public function test_already_a_member_via_other_path_still_consumes_token(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['email' => 'dual@example.com']);
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $token = 'tok-d';
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'dual@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $result = app(AcceptTeamInvitationService::class)->execute($user, $token);

        $this->assertNotNull($result->accepted_at);
    }

    public function test_email_match_is_case_insensitive(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['email' => 'Mixed@Example.com']);
        $token = 'tok-c';
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'mixed@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $result = app(AcceptTeamInvitationService::class)->execute($user, $token);

        $this->assertNotNull($result->accepted_at);
    }
}
