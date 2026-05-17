<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\ListProjectStatusesForUserAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListProjectStatusesForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_every_status_across_user_projects(): void
    {
        $user = User::factory()->create();
        [$teamA, $projectA] = $this->seedTeam($user);
        $sA = ProjectStatus::factory()->create(['project_id' => $projectA->id, 'name' => 'Backlog']);
        $sB = ProjectStatus::factory()->create(['project_id' => $projectA->id, 'name' => 'Done']);

        // Stranger's project — must not appear.
        $stranger = User::factory()->create();
        $this->seedTeam($stranger);

        $result = app(ListProjectStatusesForUserAction::class)->execute($user);

        $this->assertEqualsCanonicalizing(
            [$sA->id, $sB->id],
            $result->pluck('id')->all()
        );
    }

    public function test_scopes_to_a_single_team(): void
    {
        $user = User::factory()->create();
        [$teamA, $projectA] = $this->seedTeam($user);
        [, $projectB] = $this->seedTeam($user);
        $sA = ProjectStatus::factory()->create(['project_id' => $projectA->id]);
        ProjectStatus::factory()->create(['project_id' => $projectB->id]);

        $result = app(ListProjectStatusesForUserAction::class)->execute($user, $teamA);

        $this->assertSame([$sA->id], $result->pluck('id')->all());
    }

    /** @return array{0: Team, 1: Project} */
    private function seedTeam(User $user): array
    {
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        return [$team, $project];
    }
}
