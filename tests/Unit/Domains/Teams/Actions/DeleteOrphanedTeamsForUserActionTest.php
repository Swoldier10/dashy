<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\DeleteOrphanedTeamsForUserAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteOrphanedTeamsForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_solo_teams_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $solo = Team::factory()->create(['name' => 'Solo']);
        $solo->members()->attach($user->id, ['role' => TeamRole::Owner->value]);

        $shared = Team::factory()->create(['name' => 'Shared']);
        $shared->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $shared->members()->attach($other->id, ['role' => TeamRole::Member->value]);

        (new DeleteOrphanedTeamsForUserAction)->execute($user);

        $this->assertDatabaseMissing('teams', ['id' => $solo->id]);
        $this->assertDatabaseHas('teams', ['id' => $shared->id]);
    }

    public function test_no_op_when_user_has_no_teams(): void
    {
        $user = User::factory()->create();

        (new DeleteOrphanedTeamsForUserAction)->execute($user);

        $this->assertSame(0, Team::count());
    }

    public function test_handles_personal_team_as_solo_team(): void
    {
        $user = User::factory()->create();
        $personal = Team::factory()->personal()->create();
        $personal->members()->attach($user->id, ['role' => TeamRole::Owner->value]);

        (new DeleteOrphanedTeamsForUserAction)->execute($user);

        $this->assertDatabaseMissing('teams', ['id' => $personal->id]);
    }
}
