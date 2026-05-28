<?php

namespace Tests\Feature\Teams\Invitations;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AcceptAsExistingUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_matching_user_accepts_and_joins(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['email' => 'accept@example.com']);
        $token = 'accept-token-123';
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'accept@example.com',
            'token_hash' => hash('sha256', $token),
            'role' => TeamRole::Member,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::invite.show', ['token' => $token])
            ->call('accept')
            ->assertRedirect(route('teams.show', $team));

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => TeamRole::Member->value,
        ]);
    }

    public function test_email_mismatch_does_not_join(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['email' => 'me@example.com']);
        $token = 'mismatch-token';
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'them@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $this->actingAs($user);

        $this->get(route('invite.show', ['token' => $token]))
            ->assertOk()
            ->assertSeeText('them@example.com');

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
        ]);
    }
}
