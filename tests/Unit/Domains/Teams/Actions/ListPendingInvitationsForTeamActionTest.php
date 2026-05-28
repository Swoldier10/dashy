<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\ListPendingInvitationsForTeamAction;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPendingInvitationsForTeamActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_pending_invitations_ordered_desc(): void
    {
        $team = Team::factory()->create();
        $older = TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);
        $newer = TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        TeamInvitation::factory()->accepted()->create(['team_id' => $team->id]);
        TeamInvitation::factory()->revoked()->create(['team_id' => $team->id]);

        $list = (new ListPendingInvitationsForTeamAction)->execute($team);

        $this->assertCount(2, $list);
        $this->assertTrue($list->first()->is($newer));
        $this->assertTrue($list->last()->is($older));
    }

    public function test_scoped_to_team(): void
    {
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();
        TeamInvitation::factory()->create(['team_id' => $teamA->id]);

        $this->assertCount(0, (new ListPendingInvitationsForTeamAction)->execute($teamB));
    }
}
