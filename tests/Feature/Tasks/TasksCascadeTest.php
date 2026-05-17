<?php

namespace Tests\Feature\Tasks;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TasksCascadeTest extends TestCase
{
    use RefreshDatabase;

    private function task(Project $project): Task
    {
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        return Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);
    }

    public function test_deleting_user_keeps_task_with_null_creator_and_clears_assignment(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create();
        $task = $this->task($project);
        $task->update(['created_by_user_id' => $creator->id]);
        $task->assignees()->attach($assignee->id);

        $creator->delete();
        $assignee->delete();

        $task->refresh();
        $this->assertNull($task->created_by_user_id);
        $this->assertSame(0, $task->assignees()->count());
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_deleting_project_cascades_to_tasks_and_assignments(): void
    {
        $project = Project::factory()->create();
        $task = $this->task($project);
        $assignee = User::factory()->create();
        $task->assignees()->attach($assignee->id);
        $taskCount = Task::where('project_id', $project->id)->count();

        $project->delete();

        $this->assertSame(0, Task::where('project_id', $project->id)->count());
        $this->assertSame(0, \Illuminate\Support\Facades\DB::table('task_user')->where('task_id', $task->id)->count());
        $this->assertGreaterThanOrEqual(1, $taskCount);
    }

    public function test_deleting_team_cascades_through_projects_and_tasks(): void
    {
        $team = Team::factory()->create();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $this->task($project);

        $team->delete();

        $this->assertFalse(Project::where('team_id', $team->id)->exists());
        $this->assertFalse(Task::where('project_id', $project->id)->exists());
    }
}
