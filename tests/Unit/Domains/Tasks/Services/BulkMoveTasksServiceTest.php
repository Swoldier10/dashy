<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\BulkMoveTasksService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BulkMoveTasksServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): BulkMoveTasksService
    {
        return app(BulkMoveTasksService::class);
    }

    /**
     * @return array{0: User, 1: Project, 2: ProjectStatus, 3: ProjectStatus}
     */
    private function projectWithTwoStatuses(): array
    {
        $actor = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($actor->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $todo = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 1]);
        $done = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 2]);

        return [$actor, $project, $todo, $done];
    }

    public function test_moves_a_batch_to_the_target_status(): void
    {
        [$actor, $project, $todo, $done] = $this->projectWithTwoStatuses();
        $a = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $todo->id]);
        $b = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $todo->id]);

        $moved = $this->service()->execute($actor, [$a->id, $b->id], $done->id);

        $this->assertCount(2, $moved);
        $this->assertSame($done->id, $a->refresh()->project_status_id);
        $this->assertSame($done->id, $b->refresh()->project_status_id);
    }

    public function test_already_in_target_status_is_a_passthrough(): void
    {
        [$actor, $project, $todo, $done] = $this->projectWithTwoStatuses();
        $task = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $done->id]);

        $moved = $this->service()->execute($actor, [$task->id], $done->id);

        $this->assertCount(1, $moved);
        $this->assertSame($done->id, $task->refresh()->project_status_id);
    }

    public function test_rejects_and_rolls_back_when_a_task_belongs_to_another_project(): void
    {
        [$actor, $project, $todo, $done] = $this->projectWithTwoStatuses();
        $otherProject = Project::factory()->create(['team_id' => $project->team_id]);
        $otherStatus = ProjectStatus::factory()->create(['project_id' => $otherProject->id]);
        $inProject = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $todo->id]);
        $foreign = Task::factory()->create(['project_id' => $otherProject->id, 'project_status_id' => $otherStatus->id]);

        try {
            $this->service()->execute($actor, [$inProject->id, $foreign->id], $done->id);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException) {
            // expected
        }

        $this->assertSame($todo->id, $inProject->refresh()->project_status_id, 'First move must roll back.');
    }

    public function test_rejects_a_missing_target_status(): void
    {
        [$actor, $project, $todo] = $this->projectWithTwoStatuses();
        $task = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $todo->id]);

        $this->expectException(ValidationException::class);

        $this->service()->execute($actor, [$task->id], 999_999);
    }

    public function test_rolls_back_when_actor_is_unauthorized_on_a_task(): void
    {
        [$actor, $project, $todo, $done] = $this->projectWithTwoStatuses();
        $ok = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $todo->id]);
        $foreignProject = Project::factory()->create(); // different team, actor not a member
        $foreignStatus = ProjectStatus::factory()->create(['project_id' => $foreignProject->id]);
        $forbidden = Task::factory()->create(['project_id' => $foreignProject->id, 'project_status_id' => $foreignStatus->id]);

        try {
            $this->service()->execute($actor, [$ok->id, $forbidden->id], $done->id);
            $this->fail('Expected AuthorizationException.');
        } catch (AuthorizationException) {
            // expected
        }

        $this->assertSame($todo->id, $ok->refresh()->project_status_id, 'First move must roll back.');
    }

    public function test_rejects_an_empty_task_id_list(): void
    {
        [$actor, $project, $todo, $done] = $this->projectWithTwoStatuses();

        $this->expectException(ValidationException::class);

        $this->service()->execute($actor, [], $done->id);
    }
}
