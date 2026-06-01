<?php

namespace Tests\Feature\Teams;

use App\Domains\Teams\Enums\TeamRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackfillPersonalTeamsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_gives_a_teamless_user_a_personal_team(): void
    {
        $user = User::factory()->create(); // bare factory: no team
        $this->assertSame(0, $user->teams()->count());

        $this->artisan('teams:backfill-personal')
            ->expectsOutputToContain('Backfilled personal teams for 1 user(s).')
            ->assertExitCode(0);

        $team = $user->fresh()->teams()->first();
        $this->assertNotNull($team);
        $this->assertTrue((bool) $team->personal_team);
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => TeamRole::Owner->value,
        ]);
    }

    public function test_is_idempotent_and_skips_users_with_teams(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $originalTeamId = $user->teams()->value('teams.id');

        $this->artisan('teams:backfill-personal')
            ->expectsOutputToContain('All users already have at least one team.')
            ->assertExitCode(0);

        $this->assertSame(1, $user->fresh()->teams()->count());
        $this->assertSame($originalTeamId, $user->fresh()->teams()->value('teams.id'));
    }
}
