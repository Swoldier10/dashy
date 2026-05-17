<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Actions\MoveTaskToStatusAction;
use App\Domains\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoveTaskToStatusActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_status_and_position(): void
    {
        $project = Project::factory()->create();
        $statusA = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $statusB = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $statusA->id,
            'position' => 0,
        ]);

        $moved = (new MoveTaskToStatusAction)->execute($task, $statusB->id, 3);

        $this->assertSame($statusB->id, $moved->project_status_id);
        $this->assertSame(3, $moved->position);
    }
}
