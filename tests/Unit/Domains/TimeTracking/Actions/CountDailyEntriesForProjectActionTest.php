<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Actions\CountDailyEntriesForProjectAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountDailyEntriesForProjectActionTest extends TestCase
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

    public function test_counts_entries_per_calendar_day(): void
    {
        [$project, $task] = $this->makeProjectWithTask();

        TimeEntry::factory()->forTask($task)->create(['started_at' => CarbonImmutable::parse('2026-05-03 09:00:00')]);
        TimeEntry::factory()->forTask($task)->create(['started_at' => CarbonImmutable::parse('2026-05-03 14:00:00')]);
        TimeEntry::factory()->forTask($task)->create(['started_at' => CarbonImmutable::parse('2026-05-05 09:00:00')]);

        $result = (new CountDailyEntriesForProjectAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );

        $this->assertSame(2, $result['2026-05-03']);
        $this->assertSame(1, $result['2026-05-05']);
        $this->assertArrayNotHasKey('2026-05-04', $result);
    }

    public function test_filters_by_user_id_when_provided(): void
    {
        [$project, $task] = $this->makeProjectWithTask();
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        TimeEntry::factory()->forTask($task)->forUser($alice)->create(['started_at' => CarbonImmutable::parse('2026-05-03 09:00:00')]);
        TimeEntry::factory()->forTask($task)->forUser($alice)->create(['started_at' => CarbonImmutable::parse('2026-05-03 14:00:00')]);
        TimeEntry::factory()->forTask($task)->forUser($bob)->create(['started_at' => CarbonImmutable::parse('2026-05-03 11:00:00')]);

        $aliceOnly = (new CountDailyEntriesForProjectAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
            $alice->id,
        );
        $this->assertSame(2, $aliceOnly['2026-05-03']);

        $all = (new CountDailyEntriesForProjectAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );
        $this->assertSame(3, $all['2026-05-03']);
    }

    public function test_excludes_entries_outside_the_window_and_from_other_projects(): void
    {
        [$project, $task] = $this->makeProjectWithTask();
        [, $otherTask] = $this->makeProjectWithTask();

        TimeEntry::factory()->forTask($task)->create(['started_at' => CarbonImmutable::parse('2026-04-30 23:30:00')]);
        TimeEntry::factory()->forTask($otherTask)->create(['started_at' => CarbonImmutable::parse('2026-05-03 11:00:00')]);
        TimeEntry::factory()->forTask($task)->create(['started_at' => CarbonImmutable::parse('2026-05-03 12:00:00')]);

        $result = (new CountDailyEntriesForProjectAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );

        $this->assertSame(1, $result['2026-05-03'] ?? null, 'Only the in-window entry on the right project should count.');
        $this->assertArrayNotHasKey('2026-04-30', $result);
    }
}
