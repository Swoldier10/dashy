<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\DeleteTeamAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTeamActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_team_and_cascades_pivot_rows(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);

        (new DeleteTeamAction)->execute($team);

        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
        $this->assertDatabaseMissing('team_user', ['team_id' => $team->id]);
    }
}
