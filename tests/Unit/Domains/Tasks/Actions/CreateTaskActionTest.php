<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Actions\CreateTaskAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_task_with_provided_attributes(): void
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $user = User::factory()->create();

        $task = (new CreateTaskAction)->execute([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'created_by_user_id' => $user->id,
            'name' => 'Build feature X',
            'description' => 'Body',
            'priority' => 'high',
            'position' => 3,
        ]);

        $this->assertSame('Build feature X', $task->name);
        $this->assertSame('Body', $task->description);
        $this->assertSame('high', $task->priority->value);
        $this->assertSame($project->id, $task->project_id);
        $this->assertSame($status->id, $task->project_status_id);
        $this->assertSame($user->id, $task->created_by_user_id);
        $this->assertSame(3, $task->position);
    }

    public function test_defaults_to_normal_priority_and_zero_position(): void
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $task = (new CreateTaskAction)->execute([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'name' => 'Bare',
        ]);

        $this->assertSame('normal', $task->priority->value);
        $this->assertSame(0, $task->position);
        $this->assertNull($task->description);
        $this->assertNull($task->created_by_user_id);
    }
}
