<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\BulkAssignTasksService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BulkAssignTasksServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): BulkAssignTasksService
    {
        return app(BulkAssignTasksService::class);
    }

    public function test_assigns_a_member_to_a_batch_of_tasks(): void
    {
        $actor = User::factory()->create();
        $assignee = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach([
            $actor->id => ['role' => TeamRole::Member->value],
            $assignee->id => ['role' => TeamRole::Member->value],
        ]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $taskA = Task::factory()->create(['project_id' => $project->id]);
        $taskB = Task::factory()->create(['project_id' => $project->id]);

        $result = $this->service()->execute($actor, [$taskA->id, $taskB->id], $assignee->id);

        $this->assertCount(2, $result);
        $this->assertTrue($taskA->refresh()->assignees->contains('id', $assignee->id));
        $this->assertTrue($taskB->refresh()->assignees->contains('id', $assignee->id));
    }

    public function test_skips_already_assigned_tasks_without_duplicating(): void
    {
        $actor = User::factory()->create();
        $assignee = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach([
            $actor->id => ['role' => TeamRole::Member->value],
            $assignee->id => ['role' => TeamRole::Member->value],
        ]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);
        $task->assignees()->attach($assignee->id, ['assigned_by_user_id' => $actor->id]);

        $result = $this->service()->execute($actor, [$task->id], $assignee->id);

        $this->assertCount(1, $result);
        $this->assertSame(1, $task->refresh()->assignees()->where('users.id', $assignee->id)->count());
    }

    public function test_rolls_back_entire_batch_when_one_task_team_excludes_the_user(): void
    {
        $actor = User::factory()->create();
        $assignee = User::factory()->create();
        $teamWith = Team::factory()->create();
        $teamWithout = Team::factory()->create();
        $teamWith->members()->attach([
            $actor->id => ['role' => TeamRole::Member->value],
            $assignee->id => ['role' => TeamRole::Member->value],
        ]);
        // Actor can update both tasks, but the assignee is NOT in the second team.
        $teamWithout->members()->attach($actor->id, ['role' => TeamRole::Member->value]);
        $projectWith = Project::factory()->create(['team_id' => $teamWith->id]);
        $projectWithout = Project::factory()->create(['team_id' => $teamWithout->id]);
        $taskOk = Task::factory()->create(['project_id' => $projectWith->id]);
        $taskBad = Task::factory()->create(['project_id' => $projectWithout->id]);

        try {
            $this->service()->execute($actor, [$taskOk->id, $taskBad->id], $assignee->id);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException) {
            // expected
        }

        // The first task's assignment must be rolled back atomically.
        $this->assertFalse($taskOk->refresh()->assignees->contains('id', $assignee->id));
    }

    public function test_rolls_back_when_actor_is_unauthorized_on_a_task(): void
    {
        $actor = User::factory()->create();
        $assignee = User::factory()->create();
        $team = Team::factory()->create();
        $foreignTeam = Team::factory()->create();
        $team->members()->attach([
            $actor->id => ['role' => TeamRole::Member->value],
            $assignee->id => ['role' => TeamRole::Member->value],
        ]);
        $assignee->teams()->attach($foreignTeam->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $foreignProject = Project::factory()->create(['team_id' => $foreignTeam->id]);
        $taskOk = Task::factory()->create(['project_id' => $project->id]);
        $taskForbidden = Task::factory()->create(['project_id' => $foreignProject->id]);

        try {
            $this->service()->execute($actor, [$taskOk->id, $taskForbidden->id], $assignee->id);
            $this->fail('Expected AuthorizationException.');
        } catch (AuthorizationException) {
            // expected
        }

        $this->assertFalse($taskOk->refresh()->assignees->contains('id', $assignee->id));
    }

    public function test_rejects_an_empty_task_id_list(): void
    {
        $actor = User::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service()->execute($actor, [], $actor->id);
    }
}
