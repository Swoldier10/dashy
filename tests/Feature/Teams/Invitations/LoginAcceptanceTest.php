<?php

namespace Tests\Feature\Teams\Invitations;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_user_login_consumes_pending_invitation(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['email' => 'login-accept@example.com']);
        $token = 'login-token';
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'login-accept@example.com',
            'token_hash' => hash('sha256', $token),
            'role' => TeamRole::Member,
        ]);

        session(['invitation.pending_token' => $token]);

        event(new Login('web', $user, false));

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
        ]);
        $this->assertNull(session('invitation.pending_token'));
        $this->assertSame(route('teams.show', $team), session('url.intended'));
    }

    public function test_login_without_session_token_does_nothing(): void
    {
        $user = User::factory()->create();

        event(new Login('web', $user, false));

        $this->assertNull(session('url.intended'));
    }

    public function test_stale_invitation_bounces_to_invite_page_on_login(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['email' => 'stale@example.com']);
        $token = 'stale-token';
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'stale@example.com',
            'token_hash' => hash('sha256', $token),
            'role' => TeamRole::Member,
            'expires_at' => now()->subDay(), // expired between click and login
        ]);

        session(['invitation.pending_token' => $token]);

        event(new Login('web', $user, false));

        // Not joined — and bounced back to the invite page so they see why.
        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
        ]);
        $this->assertSame(route('invite.show', $token), session('url.intended'));
    }
}
