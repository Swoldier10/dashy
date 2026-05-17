<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Actions\CreateTimeEntryAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CreateTimeEntryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_closed_entry_with_duration(): void
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();
        $start = Carbon::now()->subHour();

        $entry = (new CreateTimeEntryAction)->execute([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'started_at' => $start,
            'ended_at' => $start->copy()->addMinutes(30),
            'duration_seconds' => 30 * 60,
            'notes' => 'Pair session',
        ]);

        $this->assertSame($task->id, $entry->task_id);
        $this->assertSame($user->id, $entry->user_id);
        $this->assertSame(30 * 60, $entry->duration_seconds);
        $this->assertSame('Pair session', $entry->notes);
        $this->assertNotNull($entry->ended_at);
    }

    public function test_creates_running_entry_with_null_ended_at(): void
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();

        $entry = (new CreateTimeEntryAction)->execute([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'started_at' => Carbon::now(),
        ]);

        $this->assertNull($entry->ended_at);
        $this->assertNull($entry->duration_seconds);
        $this->assertNull($entry->notes);
    }
}
