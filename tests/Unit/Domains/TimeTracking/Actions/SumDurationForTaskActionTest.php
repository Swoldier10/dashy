<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Actions\SumDurationForTaskAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SumDurationForTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sums_closed_entries(): void
    {
        $task = Task::factory()->create();
        TimeEntry::factory()->forTask($task)->create(['duration_seconds' => 600]);
        TimeEntry::factory()->forTask($task)->create(['duration_seconds' => 1200]);

        $this->assertSame(1800, (new SumDurationForTaskAction)->execute($task));
    }

    public function test_adds_elapsed_time_of_running_entries(): void
    {
        Carbon::setTestNow('2026-05-11 12:00:00');
        $task = Task::factory()->create();
        TimeEntry::factory()->forTask($task)->create([
            'started_at' => Carbon::now()->subMinutes(20),
            'ended_at' => Carbon::now()->subMinutes(15),
            'duration_seconds' => 300,
        ]);
        TimeEntry::factory()->forTask($task)->running()->create([
            'started_at' => Carbon::now()->subMinutes(10),
        ]);

        $this->assertSame(300 + 600, (new SumDurationForTaskAction)->execute($task));

        Carbon::setTestNow();
    }

    public function test_returns_zero_for_task_with_no_entries(): void
    {
        $task = Task::factory()->create();
        $this->assertSame(0, (new SumDurationForTaskAction)->execute($task));
    }
}
