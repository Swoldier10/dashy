<?php

namespace Tests\Feature\Teams\Invitations;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicInvitePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_token_shows_invalid_state(): void
    {
        $this->get(route('invite.show', ['token' => 'no-such-token']))
            ->assertOk()
            ->assertSeeText('Invitation not found');
    }

    public function test_expired_invitation_shows_expired_state(): void
    {
        $token = 'expired-token';
        TeamInvitation::factory()->expired()->create([
            'token_hash' => hash('sha256', $token),
        ]);

        $this->get(route('invite.show', ['token' => $token]))
            ->assertOk()
            ->assertSeeText('Invitation expired');
    }

    public function test_revoked_invitation_shows_revoked_state(): void
    {
        $token = 'revoked-token';
        TeamInvitation::factory()->revoked()->create([
            'token_hash' => hash('sha256', $token),
        ]);

        $this->get(route('invite.show', ['token' => $token]))
            ->assertOk()
            ->assertSeeText('Invitation revoked');
    }

    public function test_already_used_shows_accepted_state_for_visitor(): void
    {
        $token = 'used-token';
        $other = User::factory()->create();
        TeamInvitation::factory()->create([
            'token_hash' => hash('sha256', $token),
            'accepted_at' => now(),
            'accepted_by_user_id' => $other->id,
        ]);

        $this->get(route('invite.show', ['token' => $token]))
            ->assertOk()
            ->assertSeeText('Invitation already used');
    }

    public function test_guest_with_no_account_sees_register_cta_and_session_token_stored(): void
    {
        $token = 'register-cta-token';
        $team = Team::factory()->create();
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'brand-new@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $response = $this->get(route('invite.show', ['token' => $token]));

        $response->assertOk()->assertSeeText('Create account');
        $this->assertSame($token, session('invitation.pending_token'));
    }

    public function test_guest_with_existing_account_sees_login_cta(): void
    {
        $token = 'login-cta-token';
        User::factory()->create(['email' => 'has-account@example.com']);
        $team = Team::factory()->create();
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'has-account@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $response = $this->get(route('invite.show', ['token' => $token]));

        $response->assertOk()->assertSeeText('Sign in');
        $this->assertSame($token, session('invitation.pending_token'));
    }

    public function test_authenticated_matching_user_sees_accept_cta(): void
    {
        $token = 'ready-token';
        $user = User::factory()->create(['email' => 'ready@example.com']);
        $team = Team::factory()->create();
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'ready@example.com',
            'token_hash' => hash('sha256', $token),
            'role' => TeamRole::Member,
        ]);

        $this->actingAs($user)
            ->get(route('invite.show', ['token' => $token]))
            ->assertOk()
            ->assertSeeText('Accept invitation');
    }
}
