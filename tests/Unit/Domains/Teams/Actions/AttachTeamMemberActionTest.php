<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\AttachTeamMemberAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttachTeamMemberActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_attaches_member_with_role(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();

        (new AttachTeamMemberAction)->execute($team, $user, TeamRole::Member);

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);
    }

    public function test_unique_constraint_blocks_duplicate(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $action = new AttachTeamMemberAction;

        $action->execute($team, $user, TeamRole::Member);

        $this->expectException(QueryException::class);
        $action->execute($team, $user, TeamRole::Owner);
    }
}
