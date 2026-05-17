<?php

namespace Tests\Unit\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\StopTimerService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StopTimerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_closes_running_entry(): void
    {
        Carbon::setTestNow('2026-05-11 12:00:00');
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->forUser($user)->running()->create([
            'started_at' => Carbon::now()->subMinutes(5),
        ]);
        Carbon::setTestNow('2026-05-11 12:00:05');

        $stopped = app(StopTimerService::class)->execute($user);

        $this->assertSame($entry->id, $stopped->id);
        $this->assertNotNull($stopped->ended_at);
        $this->assertGreaterThan(0, $stopped->duration_seconds);

        Carbon::setTestNow();
    }

    public function test_throws_when_no_running_timer(): void
    {
        $user = User::factory()->create();
        $this->expectException(ValidationException::class);
        app(StopTimerService::class)->execute($user);
    }
}
