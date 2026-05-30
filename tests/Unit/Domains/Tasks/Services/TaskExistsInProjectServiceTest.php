<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\TaskExistsInProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskExistsInProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_true_when_task_belongs(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->assertTrue(
            app(TaskExistsInProjectService::class)->execute($task->id, $project->id),
        );
    }

    public function test_returns_false_when_task_in_different_project(): void
    {
        $project = Project::factory()->create();
        $other = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $other->id]);

        $this->assertFalse(
            app(TaskExistsInProjectService::class)->execute($task->id, $project->id),
        );
    }
}
