<?php

namespace Tests\Feature\Teams\Invitations;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RevokeInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_revoke_via_team_page(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();
        $invitation = TeamInvitation::factory()->create(['team_id' => $team->id]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->call('confirmRevoke', $invitation->id)
            ->call('revoke');

        $this->assertNotNull($invitation->fresh()->revoked_at);
    }

    public function test_revoked_invitation_cannot_be_accepted(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['email' => 'rv@example.com']);
        $token = 'revoke-then-accept';
        TeamInvitation::factory()->revoked()->create([
            'team_id' => $team->id,
            'email' => 'rv@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $this->actingAs($user);

        Livewire::test('pages::invite.show', ['token' => $token])
            ->call('accept')
            ->assertNoRedirect();

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
        ]);
    }

    /** @return array{0: User, 1: Team} */
    private function makeTeamWithOwner(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        return [$owner, $team];
    }
}
