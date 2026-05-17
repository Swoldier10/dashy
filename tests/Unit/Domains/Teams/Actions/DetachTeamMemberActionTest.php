<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\DetachTeamMemberAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetachTeamMemberActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_removes_pivot_row(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        (new DetachTeamMemberAction)->execute($team, $user);

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_no_op_when_not_a_member(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();

        (new DetachTeamMemberAction)->execute($team, $user);

        $this->assertSame(0, $team->members()->count());
    }
}
