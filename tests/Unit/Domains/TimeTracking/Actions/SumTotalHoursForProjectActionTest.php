<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Actions\SumTotalHoursForProjectAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SumTotalHoursForProjectActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: Project, 1: Task}
     */
    private function makeProjectWithTask(): array
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->forProject($project, $status)->create();

        return [$project, $task];
    }

    public function test_sums_closed_entries_across_tasks_in_project(): void
    {
        [$project, $task1] = $this->makeProjectWithTask();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $task2 = Task::factory()->forProject($project, $status)->create();

        TimeEntry::factory()->forTask($task1)->create(['duration_seconds' => 600]);
        TimeEntry::factory()->forTask($task2)->create(['duration_seconds' => 1200]);

        $this->assertSame(1800, (new SumTotalHoursForProjectAction)->execute($project->id));
    }

    public function test_adds_elapsed_time_of_running_entries(): void
    {
        Carbon::setTestNow('2026-05-11 12:00:00');

        [$project, $task] = $this->makeProjectWithTask();
        TimeEntry::factory()->forTask($task)->create(['duration_seconds' => 300]);
        TimeEntry::factory()->forTask($task)->running()->create([
            'started_at' => Carbon::now()->subMinutes(10),
        ]);

        $this->assertSame(300 + 600, (new SumTotalHoursForProjectAction)->execute($project->id));

        Carbon::setTestNow();
    }

    public function test_filters_by_user_id_when_provided(): void
    {
        [$project, $task] = $this->makeProjectWithTask();
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        TimeEntry::factory()->forTask($task)->forUser($alice)->create(['duration_seconds' => 600]);
        TimeEntry::factory()->forTask($task)->forUser($bob)->create(['duration_seconds' => 1200]);

        $this->assertSame(600, (new SumTotalHoursForProjectAction)->execute($project->id, $alice->id));
        $this->assertSame(1800, (new SumTotalHoursForProjectAction)->execute($project->id));
    }

    public function test_excludes_entries_from_other_projects(): void
    {
        [$project, $task] = $this->makeProjectWithTask();
        [, $otherTask] = $this->makeProjectWithTask();

        TimeEntry::factory()->forTask($task)->create(['duration_seconds' => 300]);
        TimeEntry::factory()->forTask($otherTask)->create(['duration_seconds' => 10_000]);

        $this->assertSame(300, (new SumTotalHoursForProjectAction)->execute($project->id));
    }

    public function test_returns_zero_when_no_entries(): void
    {
        [$project] = $this->makeProjectWithTask();

        $this->assertSame(0, (new SumTotalHoursForProjectAction)->execute($project->id));
    }
}
