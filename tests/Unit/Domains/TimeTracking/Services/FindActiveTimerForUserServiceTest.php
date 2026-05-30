<?php

namespace Tests\Unit\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\FindActiveTimerForUserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindActiveTimerForUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_the_running_timer_with_task_and_project_loaded(): void
    {
        $user = User::factory()->create();
        $running = TimeEntry::factory()->running()->forUser($user)->create();

        $found = app(FindActiveTimerForUserService::class)->execute($user);

        $this->assertNotNull($found);
        $this->assertSame($running->id, $found->id);
        // The service eager-loads task.project so the UI never lazy-queries.
        $this->assertTrue($found->relationLoaded('task'));
        $this->assertTrue($found->task->relationLoaded('project'));
    }

    public function test_returns_null_when_no_timer_is_running(): void
    {
        $user = User::factory()->create();
        TimeEntry::factory()->forUser($user)->create(); // stopped

        $this->assertNull(app(FindActiveTimerForUserService::class)->execute($user));
    }
}
