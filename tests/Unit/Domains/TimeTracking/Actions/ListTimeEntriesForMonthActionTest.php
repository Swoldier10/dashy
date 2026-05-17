<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Actions\ListTimeEntriesForMonthAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTimeEntriesForMonthActionTest extends TestCase
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

    public function test_returns_only_entries_in_month(): void
    {
        [$project, $task] = $this->makeProjectWithTask();

        TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-04-30 23:30:00'),
            'ended_at' => CarbonImmutable::parse('2026-04-30 23:45:00'),
            'duration_seconds' => 900,
        ]);
        $inside = TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);
        TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-06-01 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-06-01 10:00:00'),
            'duration_seconds' => 3600,
        ]);

        $result = (new ListTimeEntriesForMonthAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );

        $this->assertCount(1, $result);
        $this->assertSame($inside->id, $result->first()->id);
    }

    public function test_scopes_by_project(): void
    {
        [$project, $task] = $this->makeProjectWithTask();
        [, $otherTask] = $this->makeProjectWithTask();

        TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);
        TimeEntry::factory()->forTask($otherTask)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 11:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 12:00:00'),
            'duration_seconds' => 3600,
        ]);

        $result = (new ListTimeEntriesForMonthAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );

        $this->assertCount(1, $result);
        $this->assertSame($task->id, $result->first()->task_id);
    }

    public function test_optional_user_filter(): void
    {
        [$project, $task] = $this->makeProjectWithTask();
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        TimeEntry::factory()->forTask($task)->forUser($alice)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);
        TimeEntry::factory()->forTask($task)->forUser($bob)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 11:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 12:00:00'),
            'duration_seconds' => 3600,
        ]);

        $all = (new ListTimeEntriesForMonthAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );
        $aliceOnly = (new ListTimeEntriesForMonthAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
            $alice->id,
        );

        $this->assertCount(2, $all);
        $this->assertCount(1, $aliceOnly);
        $this->assertSame($alice->id, $aliceOnly->first()->user_id);
    }

    public function test_eager_loads_user_and_task(): void
    {
        [$project, $task] = $this->makeProjectWithTask();
        TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);

        $result = (new ListTimeEntriesForMonthAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );

        $entry = $result->first();
        $this->assertTrue($entry->relationLoaded('user'));
        $this->assertTrue($entry->relationLoaded('task'));
    }

    public function test_orders_by_started_at_ascending(): void
    {
        [$project, $task] = $this->makeProjectWithTask();

        $second = TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-05-20 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-20 10:00:00'),
            'duration_seconds' => 3600,
        ]);
        $first = TimeEntry::factory()->forTask($task)->create([
            'started_at' => CarbonImmutable::parse('2026-05-05 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-05 10:00:00'),
            'duration_seconds' => 3600,
        ]);

        $result = (new ListTimeEntriesForMonthAction)->execute(
            $project->id,
            CarbonImmutable::parse('2026-05-01 00:00:00'),
            CarbonImmutable::parse('2026-05-31 23:59:59'),
        );

        $this->assertSame([$first->id, $second->id], $result->pluck('id')->all());
    }
}
