<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Actions\ListTimeEntriesForTaskAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ListTimeEntriesForTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_entries_in_descending_order(): void
    {
        $task = Task::factory()->create();
        $older = TimeEntry::factory()->forTask($task)->create([
            'started_at' => Carbon::now()->subDays(2),
            'ended_at' => Carbon::now()->subDays(2)->addMinutes(15),
            'duration_seconds' => 900,
        ]);
        $newer = TimeEntry::factory()->forTask($task)->create([
            'started_at' => Carbon::now()->subHour(),
            'ended_at' => Carbon::now()->subHour()->addMinutes(15),
            'duration_seconds' => 900,
        ]);

        $list = (new ListTimeEntriesForTaskAction)->execute($task);

        $this->assertSame([$newer->id, $older->id], $list->pluck('id')->all());
    }

    public function test_excludes_entries_from_other_tasks(): void
    {
        $task = Task::factory()->create();
        $other = Task::factory()->create();
        TimeEntry::factory()->forTask($other)->create();
        $mine = TimeEntry::factory()->forTask($task)->create();

        $list = (new ListTimeEntriesForTaskAction)->execute($task);

        $this->assertSame([$mine->id], $list->pluck('id')->all());
    }
}
