<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Actions\NextTaskPositionAction;
use App\Domains\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NextTaskPositionActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_zero_when_empty(): void
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $this->assertSame(0, (new NextTaskPositionAction)->execute($project->id, $status->id));
    }

    public function test_returns_max_plus_one(): void
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 0]);
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 4]);

        $this->assertSame(5, (new NextTaskPositionAction)->execute($project->id, $status->id));
    }
}
