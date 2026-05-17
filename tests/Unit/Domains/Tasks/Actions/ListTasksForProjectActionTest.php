<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Actions\ListTasksForProjectAction;
use App\Domains\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTasksForProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_tasks_belonging_to_the_project_ordered_by_status_and_position(): void
    {
        $project = Project::factory()->create();
        $other = Project::factory()->create();
        $statusA = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 0]);
        $statusB = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 1]);

        $t2 = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $statusA->id, 'position' => 1]);
        $t1 = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $statusA->id, 'position' => 0]);
        $tB = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $statusB->id, 'position' => 0]);
        Task::factory()->create(['project_id' => $other->id]);

        $list = (new ListTasksForProjectAction)->execute($project);

        $this->assertCount(3, $list);
        $this->assertSame(
            [$t1->id, $t2->id, $tB->id],
            $list->pluck('id')->all()
        );
        $this->assertTrue($list->first()->relationLoaded('assignees'));
    }

    public function test_excludes_archived_tasks_by_default(): void
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 0]);

        $active = Task::factory()->forProject($project, $status)->create(['position' => 0]);
        Task::factory()->forProject($project, $status)->archived()->create(['position' => 1]);

        $list = (new ListTasksForProjectAction)->execute($project);

        $this->assertCount(1, $list);
        $this->assertSame($active->id, $list->first()->id);
    }

    public function test_includes_archived_tasks_when_flag_is_true(): void
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 0]);

        Task::factory()->forProject($project, $status)->create(['position' => 0]);
        Task::factory()->forProject($project, $status)->archived()->create(['position' => 1]);

        $list = (new ListTasksForProjectAction)->execute($project, includeArchived: true);

        $this->assertCount(2, $list);
        $this->assertTrue($list->pluck('is_archived')->contains(true));
        $this->assertTrue($list->pluck('is_archived')->contains(false));
    }
}
