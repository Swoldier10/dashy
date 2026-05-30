<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ListUserOpenTasksService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListUserOpenTasksServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_open_non_archived_tasks_excluding_done_and_archived(): void
    {
        $actor = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($actor->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $open = ProjectStatus::factory()->create(['project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value]);
        $done = ProjectStatus::factory()->create(['project_id' => $project->id, 'category' => ProjectStatusCategory::Done->value]);

        $openTask = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $open->id, 'is_archived' => false]);
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $done->id, 'is_archived' => false]);
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $open->id, 'is_archived' => true]);

        $result = app(ListUserOpenTasksService::class)->execute($actor);

        $this->assertSame([$openTask->id], $result->pluck('id')->all());
    }
}
