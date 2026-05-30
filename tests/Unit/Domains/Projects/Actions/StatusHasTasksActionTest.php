<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\StatusHasTasksAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusHasTasksActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_true_when_a_task_references_the_status(): void
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id]);

        $this->assertTrue((new StatusHasTasksAction)->execute($status));
    }

    public function test_returns_false_when_no_task_references_the_status(): void
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $this->assertFalse((new StatusHasTasksAction)->execute($status));
    }
}
