<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\FindActiveInvitationForTeamEmailAction;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindActiveInvitationForTeamEmailActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_pending_invitation_for_team_and_email(): void
    {
        $team = Team::factory()->create();
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'find@example.com',
        ]);

        $found = (new FindActiveInvitationForTeamEmailAction)->execute($team, 'find@example.com');

        $this->assertNotNull($found);
        $this->assertTrue($found->is($invitation));
    }

    public function test_ignores_accepted_invitations(): void
    {
        $team = Team::factory()->create();
        TeamInvitation::factory()->accepted()->create([
            'team_id' => $team->id,
            'email' => 'done@example.com',
        ]);

        $this->assertNull((new FindActiveInvitationForTeamEmailAction)->execute($team, 'done@example.com'));
    }

    public function test_ignores_revoked_invitations(): void
    {
        $team = Team::factory()->create();
        TeamInvitation::factory()->revoked()->create([
            'team_id' => $team->id,
            'email' => 'gone@example.com',
        ]);

        $this->assertNull((new FindActiveInvitationForTeamEmailAction)->execute($team, 'gone@example.com'));
    }

    public function test_ignores_expired_invitations(): void
    {
        $team = Team::factory()->create();
        TeamInvitation::factory()->expired()->create([
            'team_id' => $team->id,
            'email' => 'old@example.com',
        ]);

        $this->assertNull((new FindActiveInvitationForTeamEmailAction)->execute($team, 'old@example.com'));
    }

    public function test_scoped_to_team(): void
    {
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();
        TeamInvitation::factory()->create([
            'team_id' => $teamA->id,
            'email' => 'shared@example.com',
        ]);

        $this->assertNull((new FindActiveInvitationForTeamEmailAction)->execute($teamB, 'shared@example.com'));
    }
}
