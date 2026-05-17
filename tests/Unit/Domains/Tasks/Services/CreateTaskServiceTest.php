<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Services\CreateTaskService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateTaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private function setupProject(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        return [$user, $project, $status];
    }

    public function test_creates_task_for_team_member(): void
    {
        [$user, $project, $status] = $this->setupProject();

        $task = app(CreateTaskService::class)->execute($user, $project, [
            'name' => 'New task',
            'project_status_id' => $status->id,
        ]);

        $this->assertSame('New task', $task->name);
        $this->assertSame($status->id, $task->project_status_id);
        $this->assertSame($user->id, $task->created_by_user_id);
        $this->assertSame(0, $task->position);
    }

    public function test_increments_position(): void
    {
        [$user, $project, $status] = $this->setupProject();

        app(CreateTaskService::class)->execute($user, $project, ['name' => 'A', 'project_status_id' => $status->id]);
        $second = app(CreateTaskService::class)->execute($user, $project, ['name' => 'B', 'project_status_id' => $status->id]);

        $this->assertSame(1, $second->position);
    }

    public function test_non_member_cannot_create(): void
    {
        $project = Project::factory()->create();
        ProjectStatus::factory()->create(['project_id' => $project->id]);
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(CreateTaskService::class)->execute($stranger, $project, [
            'name' => 'Nope',
            'project_status_id' => $project->statuses()->first()->id,
        ]);
    }

    public function test_name_required(): void
    {
        [$user, $project, $status] = $this->setupProject();

        $this->expectException(ValidationException::class);

        app(CreateTaskService::class)->execute($user, $project, [
            'name' => '',
            'project_status_id' => $status->id,
        ]);
    }

    public function test_status_must_belong_to_project(): void
    {
        [$user, $project, ] = $this->setupProject();
        $foreignStatus = ProjectStatus::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateTaskService::class)->execute($user, $project, [
            'name' => 'Bad status',
            'project_status_id' => $foreignStatus->id,
        ]);
    }

    public function test_end_date_must_be_after_start(): void
    {
        [$user, $project, $status] = $this->setupProject();

        $this->expectException(ValidationException::class);

        app(CreateTaskService::class)->execute($user, $project, [
            'name' => 'Bad dates',
            'project_status_id' => $status->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-09',
        ]);
    }

    public function test_attaches_assignees_in_one_transaction(): void
    {
        [$user, $project, $status] = $this->setupProject();
        $teammate = User::factory()->create();
        $project->team->members()->attach($teammate->id, ['role' => TeamRole::Member->value]);

        $task = app(CreateTaskService::class)->execute($user, $project, [
            'name' => 'With assignees',
            'project_status_id' => $status->id,
            'assignee_user_ids' => [$user->id, $teammate->id],
        ]);

        $assigneeIds = $task->assignees()->pluck('users.id')->all();
        sort($assigneeIds);
        $expected = [$user->id, $teammate->id];
        sort($expected);
        $this->assertSame($expected, $assigneeIds);
    }

    public function test_rejects_assignee_who_is_not_team_member(): void
    {
        [$user, $project, $status] = $this->setupProject();
        $stranger = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateTaskService::class)->execute($user, $project, [
            'name' => 'Rejected',
            'project_status_id' => $status->id,
            'assignee_user_ids' => [$stranger->id],
        ]);
    }

    public function test_rolls_back_when_assignee_is_invalid_after_creation_attempt(): void
    {
        [$user, $project, $status] = $this->setupProject();
        $stranger = User::factory()->create();

        try {
            app(CreateTaskService::class)->execute($user, $project, [
                'name' => 'Should not persist',
                'project_status_id' => $status->id,
                'assignee_user_ids' => [$stranger->id],
            ]);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException) {
            // expected
        }

        $this->assertSame(0, \App\Domains\Tasks\Models\Task::count());
    }
}
