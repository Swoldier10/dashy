<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Actions\ListAllTasksForUserAction;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAllTasksForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_tasks_across_every_team_the_user_belongs_to(): void
    {
        $user = User::factory()->create();
        [$teamA, $projectA, $taskA] = $this->seedTeamWithTask($user);
        [$teamB, $projectB, $taskB] = $this->seedTeamWithTask($user);

        // Foreign user's task — must not appear.
        $stranger = User::factory()->create();
        $this->seedTeamWithTask($stranger);

        $result = app(ListAllTasksForUserAction::class)->execute($user);

        $this->assertEqualsCanonicalizing(
            [$taskA->id, $taskB->id],
            $result->pluck('id')->all()
        );
    }

    public function test_scopes_to_a_single_team_when_passed(): void
    {
        $user = User::factory()->create();
        [$teamA, , $taskA] = $this->seedTeamWithTask($user);
        $this->seedTeamWithTask($user);

        $result = app(ListAllTasksForUserAction::class)->execute($user, $teamA);

        $this->assertSame([$taskA->id], $result->pluck('id')->all());
    }

    public function test_excludes_archived_by_default(): void
    {
        $user = User::factory()->create();
        [, $project] = $this->seedTeamWithTask($user);
        $archived = Task::factory()->create([
            'project_id' => $project->id,
            'is_archived' => true,
        ]);

        $result = app(ListAllTasksForUserAction::class)->execute($user);

        $this->assertNotContains($archived->id, $result->pluck('id')->all());
    }

    public function test_includes_archived_when_flag_set(): void
    {
        $user = User::factory()->create();
        [, $project] = $this->seedTeamWithTask($user);
        $archived = Task::factory()->create([
            'project_id' => $project->id,
            'is_archived' => true,
        ]);

        $result = app(ListAllTasksForUserAction::class)->execute($user, null, true);

        $this->assertContains($archived->id, $result->pluck('id')->all());
    }

    /** @return array{0: Team, 1: Project, 2: Task} */
    private function seedTeamWithTask(User $user): array
    {
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        return [$team, $project, $task];
    }
}
