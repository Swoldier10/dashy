<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Actions\ReorderTasksAction;
use App\Domains\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReorderTasksActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_rewrites_positions_in_order(): void
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $a = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 0]);
        $b = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 1]);
        $c = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 2]);

        (new ReorderTasksAction)->execute($project->id, $status->id, [$c->id, $a->id, $b->id]);

        $this->assertSame(0, $c->refresh()->position);
        $this->assertSame(1, $a->refresh()->position);
        $this->assertSame(2, $b->refresh()->position);
    }

    public function test_silently_filters_forged_ids_from_other_project(): void
    {
        $project = Project::factory()->create();
        $other = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $otherStatus = ProjectStatus::factory()->create(['project_id' => $other->id]);

        $mine = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 0]);
        $foreign = Task::factory()->create(['project_id' => $other->id, 'project_status_id' => $otherStatus->id, 'position' => 7]);

        (new ReorderTasksAction)->execute($project->id, $status->id, [$foreign->id, $mine->id]);

        // Foreign task is untouched.
        $this->assertSame(7, $foreign->refresh()->position);
        // Mine got position 1 (its index in the supplied list).
        $this->assertSame(1, $mine->refresh()->position);
    }

    public function test_silently_filters_ids_from_a_different_status(): void
    {
        $project = Project::factory()->create();
        $statusA = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $statusB = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $inA = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $statusA->id, 'position' => 0]);
        $inB = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $statusB->id, 'position' => 9]);

        (new ReorderTasksAction)->execute($project->id, $statusA->id, [$inB->id, $inA->id]);

        $this->assertSame(9, $inB->refresh()->position);
        $this->assertSame(1, $inA->refresh()->position);
    }
}
