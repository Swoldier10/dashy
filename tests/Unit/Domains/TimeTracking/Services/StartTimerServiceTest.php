<?php

namespace Tests\Unit\Domains\TimeTracking\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\StartTimerService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StartTimerServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Task}
     */
    private function bootScenario(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->forProject($project, $status)->create();

        return [$user, $task];
    }

    public function test_starts_a_new_running_entry(): void
    {
        [$user, $task] = $this->bootScenario();

        $entry = app(StartTimerService::class)->execute($user, $task);

        $this->assertNull($entry->ended_at);
        $this->assertSame($user->id, $entry->user_id);
        $this->assertSame($task->id, $entry->task_id);
    }

    public function test_auto_stops_previous_running_entry(): void
    {
        [$user, $task] = $this->bootScenario();
        Carbon::setTestNow('2026-05-11 10:00:00');
        $previous = TimeEntry::factory()->forUser($user)->forTask($task)->running()->create([
            'started_at' => Carbon::now()->subMinutes(15),
        ]);

        Carbon::setTestNow('2026-05-11 10:00:30');
        app(StartTimerService::class)->execute($user, $task);

        $previous->refresh();
        $this->assertNotNull($previous->ended_at);
        $this->assertNotNull($previous->duration_seconds);
        $this->assertGreaterThan(0, $previous->duration_seconds);
        $this->assertSame(1, TimeEntry::query()->whereNull('ended_at')->count());

        Carbon::setTestNow();
    }

    public function test_non_member_cannot_start(): void
    {
        [, $task] = $this->bootScenario();
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);
        app(StartTimerService::class)->execute($stranger, $task);
    }
}
