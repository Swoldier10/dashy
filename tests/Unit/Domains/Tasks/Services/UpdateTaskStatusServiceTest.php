<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\UpdateTaskStatusService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateTaskStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_changes_status_and_assigns_next_position(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $a = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $b = ProjectStatus::factory()->create(['project_id' => $project->id]);
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $b->id, 'position' => 0]);
        $task = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $a->id, 'position' => 0]);

        $updated = app(UpdateTaskStatusService::class)->execute($user, $task->id, $b->id);

        $this->assertSame($b->id, $updated->project_status_id);
        $this->assertSame(1, $updated->position);
    }

    public function test_rejects_status_from_other_project(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);
        $foreign = ProjectStatus::factory()->create();

        $this->expectException(ValidationException::class);

        app(UpdateTaskStatusService::class)->execute($user, $task->id, $foreign->id);
    }
}
