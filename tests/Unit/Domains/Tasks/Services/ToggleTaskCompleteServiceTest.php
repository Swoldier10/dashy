<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ToggleTaskCompleteService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ToggleTaskCompleteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_a_pending_task_as_done(): void
    {
        [$user, $project] = $this->seedUserAndProject();
        $todo = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'position' => 0,
        ]);
        $done = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Done->value,
            'position' => 0,
        ]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $todo->id,
        ]);

        $updated = app(ToggleTaskCompleteService::class)->execute($user, $task->id);

        $this->assertSame($done->id, $updated->project_status_id);
    }

    public function test_un_marks_a_done_task_back_to_active(): void
    {
        [$user, $project] = $this->seedUserAndProject();
        $todo = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'position' => 0,
        ]);
        $done = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Done->value,
            'position' => 0,
        ]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $done->id,
        ]);

        $updated = app(ToggleTaskCompleteService::class)->execute($user, $task->id);

        $this->assertSame($todo->id, $updated->project_status_id);
    }

    public function test_falls_back_to_not_started_when_uncompleting_with_no_active_status(): void
    {
        [$user, $project] = $this->seedUserAndProject();
        $backlog = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::NotStarted->value,
            'position' => 0,
        ]);
        $done = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Done->value,
            'position' => 0,
        ]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $done->id,
        ]);

        $updated = app(ToggleTaskCompleteService::class)->execute($user, $task->id);

        $this->assertSame($backlog->id, $updated->project_status_id);
    }

    public function test_throws_when_no_done_status_exists_to_complete_into(): void
    {
        [$user, $project] = $this->seedUserAndProject();
        $todo = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
        ]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $todo->id,
        ]);

        $this->expectException(ValidationException::class);

        app(ToggleTaskCompleteService::class)->execute($user, $task->id);
    }

    /** @return array{0: User, 1: Project} */
    private function seedUserAndProject(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        return [$user, $project];
    }
}
