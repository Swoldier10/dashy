<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\AssignTaskService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AssignTaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigns_existing_team_member(): void
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

        app(AssignTaskService::class)->execute($actor, $task->id, $assignee->id);

        $this->assertTrue($task->refresh()->assignees->contains('id', $assignee->id));
    }

    public function test_rejects_user_outside_the_team(): void
    {
        $actor = User::factory()->create();
        $stranger = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($actor->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->expectException(ValidationException::class);

        app(AssignTaskService::class)->execute($actor, $task->id, $stranger->id);
    }
}
