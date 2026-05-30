<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ListOverdueTasksService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ListOverdueTasksServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_open_tasks_past_their_due_date(): void
    {
        $actor = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($actor->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $open = ProjectStatus::factory()->create(['project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value]);
        $done = ProjectStatus::factory()->create(['project_id' => $project->id, 'category' => ProjectStatusCategory::Done->value]);

        $overdueOpen = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $open->id, 'end_date' => Carbon::now()->subDay()]);
        // Overdue but done → excluded; future open → excluded.
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $done->id, 'end_date' => Carbon::now()->subDay()]);
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $open->id, 'end_date' => Carbon::now()->addWeek()]);

        $result = app(ListOverdueTasksService::class)->execute($actor);

        $this->assertSame([$overdueOpen->id], $result->pluck('id')->all());
    }

    public function test_only_mine_narrows_to_the_actors_assignments(): void
    {
        $actor = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($actor->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $open = ProjectStatus::factory()->create(['project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value]);

        $mine = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $open->id, 'end_date' => Carbon::now()->subDay()]);
        $mine->assignees()->attach($actor->id, ['assigned_by_user_id' => $actor->id]);
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $open->id, 'end_date' => Carbon::now()->subDay()]);

        $result = app(ListOverdueTasksService::class)->execute($actor, onlyMine: true);

        $this->assertSame([$mine->id], $result->pluck('id')->all());
    }
}
