<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\ListProjectsForUserAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListProjectsForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_projects_in_users_teams(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $myTeam = Team::factory()->create();
        $myTeam->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $theirTeam = Team::factory()->create();
        $theirTeam->members()->attach($other->id, ['role' => TeamRole::Owner->value]);

        $mine = Project::factory()->create(['team_id' => $myTeam->id, 'name' => 'Mine']);
        Project::factory()->create(['team_id' => $theirTeam->id, 'name' => 'Theirs']);

        $projects = (new ListProjectsForUserAction)->execute($user);

        $this->assertCount(1, $projects);
        $this->assertSame($mine->id, $projects->first()->id);
    }

    public function test_orders_by_team_id_then_created_at(): void
    {
        $user = User::factory()->create();
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();
        $teamA->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $teamB->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $bOlder = Project::factory()->create([
            'team_id' => $teamB->id,
            'name' => 'B-older',
            'created_at' => now()->subDays(2),
        ]);
        $bNewer = Project::factory()->create([
            'team_id' => $teamB->id,
            'name' => 'B-newer',
            'created_at' => now()->subDay(),
        ]);
        $a = Project::factory()->create([
            'team_id' => $teamA->id,
            'name' => 'A',
            'created_at' => now(),
        ]);

        $names = (new ListProjectsForUserAction)->execute($user)->pluck('name')->all();

        // Lower team_id (teamA) first; within team, oldest first.
        $this->assertSame(['A', 'B-older', 'B-newer'], $names);
        $this->assertSame([$a->id, $bOlder->id, $bNewer->id],
            (new ListProjectsForUserAction)->execute($user)->pluck('id')->all());
    }

    public function test_returns_empty_when_user_has_no_teams(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($other->id, ['role' => TeamRole::Owner->value]);
        Project::factory()->create(['team_id' => $team->id]);

        $projects = (new ListProjectsForUserAction)->execute($user);

        $this->assertCount(0, $projects);
    }
}
