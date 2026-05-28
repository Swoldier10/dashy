<?php

namespace Tests\Feature\Teams\Invitations;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptAsNewUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_token_is_consumed_on_register(): void
    {
        $team = Team::factory()->create();
        $token = 'new-user-token';
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'newcomer@example.com',
            'token_hash' => hash('sha256', $token),
            'role' => TeamRole::Member,
        ]);

        session(['invitation.pending_token' => $token]);

        $newUser = User::factory()->create(['email' => 'newcomer@example.com']);

        event(new Registered($newUser));

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $newUser->id,
            'role' => TeamRole::Member->value,
        ]);
        $this->assertDatabaseHas('team_invitations', [
            'email' => 'newcomer@example.com',
            'accepted_by_user_id' => $newUser->id,
        ]);
        $this->assertNull(session('invitation.pending_token'));
        $this->assertSame(route('teams.show', $team), session('url.intended'));
    }

    public function test_mismatched_email_during_register_does_not_join(): void
    {
        $team = Team::factory()->create();
        $token = 'mm-token';
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'bound@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        session(['invitation.pending_token' => $token]);

        $newUser = User::factory()->create(['email' => 'different@example.com']);

        event(new Registered($newUser));

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $newUser->id,
        ]);
        $this->assertDatabaseHas('team_invitations', [
            'email' => 'bound@example.com',
            'accepted_at' => null,
        ]);
    }
}
