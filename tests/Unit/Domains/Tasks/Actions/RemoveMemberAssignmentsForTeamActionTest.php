<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Actions\RemoveMemberAssignmentsForTeamAction;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveMemberAssignmentsForTeamActionTest extends TestCase
{
    use RefreshDatabase;

    private function taskInTeam(Team $team): Task
    {
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        return Task::factory()->forProject($project, $status)->create();
    }

    public function test_removes_only_target_users_assignments_within_the_team(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();
        $team = Team::factory()->create();
        $task1 = $this->taskInTeam($team);
        $task2 = $this->taskInTeam($team);
        $task1->assignees()->attach([$member->id, $other->id]);
        $task2->assignees()->attach($member->id);

        $removed = app(RemoveMemberAssignmentsForTeamAction::class)->execute($team->id, $member->id);

        $this->assertSame(2, $removed);
        $this->assertDatabaseMissing('task_user', ['task_id' => $task1->id, 'user_id' => $member->id]);
        $this->assertDatabaseMissing('task_user', ['task_id' => $task2->id, 'user_id' => $member->id]);
        $this->assertDatabaseHas('task_user', ['task_id' => $task1->id, 'user_id' => $other->id]);
    }

    public function test_does_not_touch_assignments_in_other_teams(): void
    {
        $member = User::factory()->create();
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();
        $taskA = $this->taskInTeam($teamA);
        $taskB = $this->taskInTeam($teamB);
        $taskA->assignees()->attach($member->id);
        $taskB->assignees()->attach($member->id);

        app(RemoveMemberAssignmentsForTeamAction::class)->execute($teamA->id, $member->id);

        $this->assertDatabaseMissing('task_user', ['task_id' => $taskA->id, 'user_id' => $member->id]);
        $this->assertDatabaseHas('task_user', ['task_id' => $taskB->id, 'user_id' => $member->id]);
    }

    public function test_returns_zero_when_team_has_no_tasks(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();

        $this->assertSame(0, app(RemoveMemberAssignmentsForTeamAction::class)->execute($team->id, $user->id));
    }
}
