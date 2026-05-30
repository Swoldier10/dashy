<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Actions\TaskExistsInProjectAction;
use App\Domains\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskExistsInProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_true_when_task_belongs_to_project(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->assertTrue((new TaskExistsInProjectAction)->execute($task->id, $project->id));
    }

    public function test_returns_false_when_task_belongs_to_different_project(): void
    {
        $project = Project::factory()->create();
        $other = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $other->id]);

        $this->assertFalse((new TaskExistsInProjectAction)->execute($task->id, $project->id));
    }

    public function test_returns_false_for_unknown_task(): void
    {
        $project = Project::factory()->create();

        $this->assertFalse((new TaskExistsInProjectAction)->execute(999_999, $project->id));
    }
}
