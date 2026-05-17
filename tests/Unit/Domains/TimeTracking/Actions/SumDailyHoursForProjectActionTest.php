<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Actions\SumDailyHoursForProjectAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SumDailyHoursForProjectActionTest extends TestCase
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

    public function test_buckets_entries_by_calendar_day(): void
    {
        [$project, $task] = $this->makeProjectWithTask();

        TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-05-03 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-03 10:00:00'),
            'duration_seconds' => 3600,
        ]);
        TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-05-03 14:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-03 15:30:00'),
            'duration_seconds' => 5400,
        ]);
        TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-05-05 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-05 10:00:00'),
            'duration_seconds' => 3600,
        ]);

        $result = (new SumDailyHoursForProjectAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );

        $this->assertSame(9000, $result['2026-05-03']);
        $this->assertSame(3600, $result['2026-05-05']);
        $this->assertArrayNotHasKey('2026-05-04', $result);
    }

    public function test_adds_elapsed_time_of_running_entries(): void
    {
        Carbon::setTestNow('2026-05-11 12:00:00');

        [$project, $task] = $this->makeProjectWithTask();

        TimeEntry::factory()->forTask($task)->running()->create([
            'started_at' => Carbon::parse('2026-05-11 11:50:00'),
        ]);

        $result = (new SumDailyHoursForProjectAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );

        $this->assertSame(600, $result['2026-05-11']);

        Carbon::setTestNow();
    }

    public function test_filters_by_user_id_when_provided(): void
    {
        [$project, $task] = $this->makeProjectWithTask();

        $alice = User::factory()->create();
        $bob = User::factory()->create();

        TimeEntry::factory()->forTask($task)->forUser($alice)->create([
            'started_at' => CarbonImmutable::parse('2026-05-03 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-03 10:00:00'),
            'duration_seconds' => 3600,
        ]);
        TimeEntry::factory()->forTask($task)->forUser($bob)->create([
            'started_at' => CarbonImmutable::parse('2026-05-03 11:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-03 12:00:00'),
            'duration_seconds' => 3600,
        ]);

        $aliceOnly = (new SumDailyHoursForProjectAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
            $alice->id,
        );

        $this->assertSame(3600, $aliceOnly['2026-05-03']);

        $all = (new SumDailyHoursForProjectAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );

        $this->assertSame(7200, $all['2026-05-03']);
    }

    public function test_excludes_entries_outside_the_window(): void
    {
        [$project, $task] = $this->makeProjectWithTask();

        TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-04-30 23:30:00'),
            'ended_at' => CarbonImmutable::parse('2026-04-30 23:45:00'),
            'duration_seconds' => 900,
        ]);
        TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-06-01 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-06-01 10:00:00'),
            'duration_seconds' => 3600,
        ]);

        $result = (new SumDailyHoursForProjectAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );

        $this->assertSame([], $result);
    }

    public function test_excludes_entries_from_other_projects(): void
    {
        [$project, $task] = $this->makeProjectWithTask();
        [, $otherTask] = $this->makeProjectWithTask();

        TimeEntry::factory()->forTask($otherTask)->create([
            'started_at' => CarbonImmutable::parse('2026-05-03 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-03 10:00:00'),
            'duration_seconds' => 3600,
        ]);
        TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-05-03 11:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-03 11:30:00'),
            'duration_seconds' => 1800,
        ]);

        $result = (new SumDailyHoursForProjectAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );

        $this->assertSame(1800, $result['2026-05-03']);
    }

    public function test_returns_empty_array_when_no_entries(): void
    {
        [$project] = $this->makeProjectWithTask();

        $result = (new SumDailyHoursForProjectAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );

        $this->assertSame([], $result);
    }
}
