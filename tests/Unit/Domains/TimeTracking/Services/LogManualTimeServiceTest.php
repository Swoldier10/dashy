<?php

namespace Tests\Unit\Domains\TimeTracking\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\TimeTracking\Services\LogManualTimeService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LogManualTimeServiceTest extends TestCase
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

    public function test_logs_entry_from_duration_string(): void
    {
        [$user, $task] = $this->bootScenario();

        $entry = app(LogManualTimeService::class)->execute($user, $task, [
            'duration' => '3h 20m',
            'notes' => 'Pair review',
        ]);

        $this->assertSame(3 * 3600 + 20 * 60, $entry->duration_seconds);
        $this->assertNotNull($entry->ended_at);
        $this->assertSame('Pair review', $entry->notes);
    }

    public function test_logs_entry_from_explicit_start_and_end(): void
    {
        [$user, $task] = $this->bootScenario();

        $entry = app(LogManualTimeService::class)->execute($user, $task, [
            'started_at' => '2026-05-11 09:00:00',
            'ended_at' => '2026-05-11 10:30:00',
        ]);

        $this->assertSame(90 * 60, $entry->duration_seconds);
    }

    public function test_rejects_end_before_start(): void
    {
        [$user, $task] = $this->bootScenario();

        $this->expectException(ValidationException::class);
        app(LogManualTimeService::class)->execute($user, $task, [
            'started_at' => '2026-05-11 10:00:00',
            'ended_at' => '2026-05-11 09:00:00',
        ]);
    }

    public function test_rejects_invalid_duration_string(): void
    {
        [$user, $task] = $this->bootScenario();

        $this->expectException(ValidationException::class);
        app(LogManualTimeService::class)->execute($user, $task, [
            'duration' => 'asdf',
        ]);
    }

    public function test_rejects_when_neither_duration_nor_dates_supplied(): void
    {
        [$user, $task] = $this->bootScenario();

        $this->expectException(ValidationException::class);
        app(LogManualTimeService::class)->execute($user, $task, []);
    }

    public function test_non_member_cannot_log_time(): void
    {
        [, $task] = $this->bootScenario();
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);
        app(LogManualTimeService::class)->execute($stranger, $task, [
            'duration' => '1h',
        ]);
    }
}
